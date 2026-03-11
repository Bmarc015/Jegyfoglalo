<template>
  <div class="table-shell">
    <div class="table-responsive my-table-container">
      <table class="table table-hover my-table">
        <thead class="sticky-top my-table-head">
          <tr class="align-middle text-center">
            <th v-if="toolsColumnVisible">Tools</th>
            <template v-for="col in columns">
              <th
                v-if="col.debug >= 1"
                :key="col.key"
                class="my-pointer"
                :class="{ 'my-debug': col.debug == 1 }"
                @click="$emit('sort', col.key)"
              >
                <div
                  class="d-flex align-items-center justify-content-center text-nowrap"
                >
                  <span>{{ col.label }}</span>
                  <span :class="{ invisible: sortColumn !== col.key }" class="ms-1">
                    {{ sortDirection === "asc" ? "↑" : "↓" }}
                  </span>
                </div>
              </th>
            </template>
          </tr>
        </thead>

        <tbody class="table-group-divider">
          <tr
            v-for="item in items"
            :key="item.id"
            :class="{ 'table-primary': selectedId === item.id }"
            @click="onClickRow(item.id)"
          >
            <td v-if="toolsColumnVisible">
              <ButtonsCrud
                :id="item.id"
                :cButtonVisible="cButtonVisible"
                :uButtonVisible="uButtonVisible"
                :dButtonVisible="dButtonVisible"
                :pButtonVisible="pButtonVisible"
                @delete="$emit('delete', $event)"
                @update="$emit('update', $event)"
                @create="$emit('create', $event)"
                @passwordChange="$emit('passwordChange', $event)"
              />
            </td>

            <template v-for="col in columns">
              <td
                v-if="col.debug >= 1"
                :key="col.key"
                :class="{ 'my-debug': col.debug == 1 }"
              >
                <!-- Custom slot for image columns -->
                <img 
                  v-if="col.type === 'image' && item[col.key]" 
                  :src="'/csapat kepek/' + item[col.key]" 
                  :alt="col.label"
                  style="max-height: 40px; max-width: 50px; object-fit: contain;"
                />
                <span v-else>{{ item[col.key] }}</span>
              </td>
            </template>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script>
import ButtonsCrud from "./ButtonsCrud.vue";

export default {
  name: "GenericTable",
  components: {
    ButtonsCrud,
  },
  props: {
    items: { type: Array, required: true },
    columns: { type: Array, required: true },
    useCollectionStore: { type: Function, required: true },
    cButtonVisible: { type: Boolean, default: true },
    uButtonVisible: { type: Boolean, default: true },
    dButtonVisible: { type: Boolean, default: true },
    pButtonVisible: { type: Boolean, default: false },
    toolsColumnVisible: { type: Boolean, default: true },
  },
  data() {
    return {
      selectedId: null,
      store: null,
    };
  },
  created() {
    if (this.useCollectionStore) {
      this.store = this.useCollectionStore();
    }
  },
  computed: {
    sortColumn() {
      return this.store ? this.store.sortColumn : "";
    },
    sortDirection() {
      return this.store ? this.store.sortDirection : "asc";
    },
  },
  methods: {
    onClickRow(id) {
      this.selectedId = id;
    },
  },
};
</script>

<style scoped>
.table-shell {
  width: 100%;
  max-width: 100%;
  margin: 0;
}

.my-table-container {
  width: 100%;
  max-width: 100%;
  max-height: none;
  overflow-y: visible;
  border: 1px solid #d6dbe3;
  border-radius: 14px;
  box-shadow: 0 10px 26px rgba(15, 23, 42, 0.08);
  background: linear-gradient(180deg, #f7f9fc 0%, #eef2f7 100%);
  padding: 6px;
}

.my-table {
  width: 100%;
  min-width: 100%;
  margin: 0;
  border-collapse: separate;
  border-spacing: 0;
  background: #ffffff;
  border-radius: 10px;
  overflow: hidden;
  table-layout: fixed;
}

.my-table-head th {
  position: sticky;
  top: 0;
  z-index: 10;
  background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
  color: #f8fafc;
  font-weight: 600;
  letter-spacing: 0.02em;
  text-align: center;
  vertical-align: middle;
  padding: 0.8rem 0.6rem;
}

.my-table tbody td {
  text-align: center;
  vertical-align: middle;
  padding: 0.75rem 0.6rem;
}

.my-table tbody tr:nth-child(even) td {
  background-color: #f8fafc;
}

.my-table tbody tr:hover td {
  background-color: #eaf2ff;
  transition: background-color 120ms ease-in-out;
}

.my-pointer {
  user-select: none;
}

@media (max-width: 768px) {
  .my-table-container {
    max-height: none;
    border-radius: 10px;
  }

  .my-table-head th,
  .my-table tbody td {
    padding: 0.62rem 0.45rem;
    font-size: 0.92rem;
  }
}
</style>
