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

<style scoped>
@import url("https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;600;700&family=Work+Sans:wght@400;600&display=swap");

.checkout-page {
  --ink: #111827;
  --muted: #5b6474;
  --accent: #0ea5a4;
  --card: #ffffff;
  --shadow: 0 20px 45px rgba(15, 23, 42, 0.12);
  position: relative;
  max-width: 1080px;
  margin: 0 auto;
  padding: 40px 20px 64px;
  font-family: "Work Sans", "Segoe UI", sans-serif;
  color: var(--ink);
}

.checkout-page::before {
  content: "";
  position: absolute;
  inset: 0 0 auto 0;
  height: 260px;
  background: radial-gradient(circle at top left, #d9f7f6, transparent 55%),
    radial-gradient(circle at top right, #ffe8cc, transparent 48%);
  z-index: -1;
}

.checkout-hero {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  gap: 20px;
  margin-bottom: 28px;
}

.eyebrow {
  font-size: 12px;
  letter-spacing: 0.2em;
  text-transform: uppercase;
  color: var(--muted);
  margin: 0 0 6px;
}

.checkout-hero h1 {
  margin: 0 0 6px;
  font-size: 34px;
  font-family: "Space Grotesk", "Segoe UI", sans-serif;
}

.subtext {
  margin: 0;
  color: var(--muted);
}

.link-back {
  text-decoration: none;
  color: var(--ink);
  font-weight: 600;
}

.checkout-grid {
  display: grid;
  grid-template-columns: minmax(0, 2fr) minmax(0, 1fr);
  gap: 24px;
}

.checkout-card,
.summary-card {
  background: var(--card);
  border-radius: 18px;
  padding: 24px;
  box-shadow: var(--shadow);
  animation: fadeUp 0.5s ease-out;
}

.match-block + .match-block {
  margin-top: 22px;
  padding-top: 22px;
  border-top: 1px solid #e2e8f0;
}

.match-card {
  padding: 16px;
  border-radius: 14px;
  background: #f8fafc;
  display: grid;
  gap: 12px;
}

.team-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  text-align: center;
}

.team {
  display: grid;
  gap: 8px;
  justify-items: center;
  font-weight: 600;
}

.team img {
  width: 44px;
  height: 44px;
  object-fit: contain;
}

.versus {
  font-size: 14px;
  color: var(--muted);
}

.meta-row {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
}

.meta-pill {
  padding: 6px 12px;
  border-radius: 999px;
  background: #e2e8f0;
  font-size: 12px;
  color: var(--muted);
}

.detail-list {
  margin-top: 18px;
  display: grid;
  gap: 10px;
}

.detail-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 15px;
}

.seat-list {
  margin-top: 18px;
  display: grid;
  gap: 10px;
}

.seat-title {
  font-weight: 600;
}

.seat-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.seat-tag {
  background: #f1f5f9;
  border-radius: 999px;
  padding: 6px 12px;
  font-size: 12px;
}

.item-actions {
  margin-top: 12px;
  display: flex;
  justify-content: flex-end;
}

.btn-remove {
  border: 1px solid #e2e8f0;
  background: #ffffff;
  color: #ef4444;
  font-weight: 600;
  border-radius: 999px;
  padding: 6px 14px;
  cursor: pointer;
}

.btn-remove:hover {
  background: #fef2f2;
}

.summary-card h2 {
  margin: 0 0 14px;
  font-size: 20px;
  font-family: "Space Grotesk", "Segoe UI", sans-serif;
}

.summary-row,
.summary-total {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 10px;
}

.summary-total {
  padding-top: 8px;
  margin-top: 6px;
  border-top: 1px solid #e2e8f0;
  font-size: 18px;
}

.btn-primary {
  width: 100%;
  border: none;
  border-radius: 12px;
  padding: 12px 18px;
  background: linear-gradient(135deg, #1a2d4d 0%, #244b85 100%);
  color: #fff;
  font-weight: 600;
  cursor: pointer;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.btn-primary:hover {
  transform: translateY(-1px);
  box-shadow: 0 16px 30px rgba(26, 45, 77, 0.25);
}

.btn-ghost {
  width: 100%;
  margin-top: 10px;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 10px 18px;
  background: #ffffff;
  color: #6b7280;
  font-weight: 600;
  cursor: pointer;
}

.btn-ghost:hover {
  background: #f8fafc;
}

.hint {
  margin: 10px 0 0;
  color: var(--muted);
  font-size: 12px;
}

.empty-state {
  background: var(--card);
  border-radius: 18px;
  padding: 28px;
  text-align: center;
  box-shadow: var(--shadow);
}

.btn-secondary {
  display: inline-flex;
  margin-top: 12px;
  padding: 10px 16px;
  border-radius: 10px;
  background: #111827;
  color: #fff;
  text-decoration: none;
}

.btn-secondary:hover {
  background: #0f172a;
}

@keyframes fadeUp {
  from {
    opacity: 0;
    transform: translateY(12px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@media (max-width: 900px) {
  .checkout-hero {
    flex-direction: column;
    align-items: flex-start;
  }

  .checkout-grid {
    grid-template-columns: 1fr;
  }
}
</style>
