<template>
  <section class="checkout-page">
    <div class="checkout-hero">
      <div>
        <p class="eyebrow">TicketMaster</p>
        <h1>Checkout</h1>
        <p class="subtext">Review your seats and confirm the order.</p>
      </div>
      <router-link class="link-back" to="/adatok/buytickets">
        Back to tickets
      </router-link>
    </div>

    <div v-if="cartItems.length" class="checkout-grid">
      <div class="checkout-card">
        <div v-for="(item, index) in cartItems" :key="itemKey(item, index)" class="match-block">
          <div class="match-card">
            <div class="team-row">
              <div class="team">
                <img v-if="getLogo(item.match?.homeTeam)" :src="getLogo(item.match?.homeTeam)" alt="Home team logo" />
                <span class="team-name">{{ item.match?.homeTeam }}</span>
              </div>
              <span class="versus">vs</span>
              <div class="team">
                <img v-if="getLogo(item.match?.awayTeam)" :src="getLogo(item.match?.awayTeam)" alt="Away team logo" />
                <span class="team-name">{{ item.match?.awayTeam }}</span>
              </div>
            </div>
          <div class="meta-row">
            <span class="meta-pill">Kickoff {{ item.match?.time || "--:--" }}</span>
            <span v-if="formatMatchDate(item.match?.date)" class="meta-pill">
              {{ formatMatchDate(item.match?.date) }}
            </span>
            <span class="meta-pill">{{ item.match?.venue || "Venue TBD" }}</span>
          </div>
          </div>

          <div class="detail-list">
            <div class="detail-row">
              <span>Sector</span>
              <strong>{{ item.sectorName || "-" }}</strong>
            </div>
            <div class="detail-row">
              <span>Seats</span>
              <strong>{{ (item.seats || []).length }}x</strong>
            </div>
            <div class="detail-row">
              <span>Price/seat</span>
              <strong>{{ formatCurrency(item.sectorPrice) }}</strong>
            </div>
          </div>

          <div class="seat-list">
            <span class="seat-title">Selected seats</span>
            <div class="seat-tags">
              <span v-for="seat in item.seats" :key="seat.id" class="seat-tag">
                Row {{ seat.row }}, Col {{ seat.col }}
              </span>
            </div>
          </div>
          <div class="item-actions">
            <button class="btn-remove" type="button" @click="removeItem(index)">
              Remove match
            </button>
          </div>
        </div>
      </div>

      <div class="summary-card">
        <h2>Order summary</h2>
        <div class="summary-row">
          <span>Tickets ({{ totalSeatCount }}x)</span>
          <strong>{{ formatCurrency(ticketsSubtotal) }}</strong>
        </div>
        <div class="summary-row">
          <span>Service fee</span>
          <strong>{{ formatCurrency(0) }}</strong>
        </div>
        <div class="summary-total">
          <span>Total</span>
          <strong>{{ formatCurrency(grandTotal) }}</strong>
        </div>
        <button class="btn-primary" @click="confirmPurchase">
          Complete purchase
        </button>
        <button class="btn-ghost" type="button" @click="clearCart">
          Clear cart
        </button>
        <p class="hint">Payment integration coming soon.</p>
      </div>
    </div>

    <div v-else class="empty-state">
      <p>Your checkout is empty. Pick seats first.</p>
      <router-link class="btn-secondary" to="/adatok/buytickets">
        Back to tickets
      </router-link>
    </div>
  </section>
</template>

<script>
import { mapState } from "pinia";
import { resolveTeamLogo } from "@/constants/teamLogos";
import { useUserLoginLogoutStore } from "@/stores/userLoginLogoutStore";
import apiClient from "@/api/axiosClient";
import { useToastStore } from "@/stores/toastStore";

export default {
  name: "CheckoutView",
  data() {
    return {
      cart: null,
    };
  },
  created() {
    this.loadCart();
  },
  computed: {
    ...mapState(useUserLoginLogoutStore, ["item", "isLoggedIn"]),
    cartItems() {
      if (!this.cart) return [];
      return Array.isArray(this.cart) ? this.cart : [this.cart];
    },
    totalSeatCount() {
      return this.cartItems.reduce(
        (sum, item) => sum + (Array.isArray(item?.seats) ? item.seats.length : 0),
        0,
      );
    },
    ticketsSubtotal() {
      return this.cartItems.reduce((sum, item) => {
        const count = Array.isArray(item?.seats) ? item.seats.length : 0;
        const price = Number(item?.sectorPrice) || 0;
        return sum + count * price;
      }, 0);
    },
    grandTotal() {
      return this.ticketsSubtotal;
    },
  },
  methods: {
    loadCart() {
      try {
        const raw = sessionStorage.getItem("checkoutCart");
        this.cart = raw ? JSON.parse(raw) : null;
      } catch (error) {
        this.cart = null;
      }
    },
    formatCurrency(value) {
      return new Intl.NumberFormat("de-DE", {
        style: "currency",
        currency: "EUR",
      }).format(value || 0);
    },
    formatMatchDate(dateValue) {
      if (!dateValue) return "";
      const parsed = new Date(`${dateValue}T00:00:00`);
      if (Number.isNaN(parsed.getTime())) return String(dateValue);
      return parsed.toLocaleDateString("en-US", {
        year: "numeric",
        month: "long",
        day: "numeric",
      });
    },
    getLogo(teamName) {
      return teamName ? resolveTeamLogo(teamName) : "";
    },
    itemKey(item, index) {
      return `${item?.match?.id ?? "match"}-${item?.sectorName ?? "sector"}-${index}`;
    },
    async confirmPurchase() {
      const toast = useToastStore();
      if (!this.isLoggedIn) {
        this.$router.push({ path: "/login", query: { redirect: "/checkout" } });
        return;
      }
      if (!this.cartItems.length) {
        toast.messages.push("No valid ticket selection found.");
        toast.show("Error");
        return;
      }

      try {
        for (const item of this.cartItems) {
          const matchId = item?.match?.id;
          const seatIds = item?.seatIds;
          if (!matchId || !Array.isArray(seatIds) || seatIds.length === 0) continue;
          await apiClient.post("/tickets/book", {
            game_id: matchId,
            seat_ids: seatIds,
            user_id: this.item?.id,
          });
        }
        toast.messages.push("Booking successful!");
        toast.show("Success");
        sessionStorage.removeItem("checkoutCart");
        window.dispatchEvent(new Event("cart-updated"));
        this.$router.push("/adatok/buytickets");
      } catch (error) {
        toast.messages.push("Booking failed. Please try again.");
        toast.show("Error");
      }
    },
    clearCart() {
      sessionStorage.removeItem("checkoutCart");
      this.cart = null;
      window.dispatchEvent(new Event("cart-updated"));
    },
    removeItem(index) {
      const items = [...this.cartItems];
      items.splice(index, 1);
      this.cart = items.length ? items : null;
      if (items.length) {
        sessionStorage.setItem("checkoutCart", JSON.stringify(items));
      } else {
        sessionStorage.removeItem("checkoutCart");
      }
      window.dispatchEvent(new Event("cart-updated"));
    },
  },
};
</script>

<style scoped src="../assets/checkout.css">

</style>
