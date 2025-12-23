<script setup>
import { computed, toRef, ref } from "vue";
import { Icon } from "am-ui-package";

const props = defineProps({
  data: { type: [Array, Object], default: () => [] },
  characteristics: { type: Array, default: () => [] },
  currentSegment: { type: Object, default: () => ({}) },
  colsPattern: { type: Array, default: () => [] },
  onlyType: { type: [String, Array], default: "PREFERRED" },
  title: { type: String, default: "Preferente" },
  subtitle: { type: String, default: "Ubicación prioritaria" },
  initials: { type: String },
});

const currentSegmentRef = toRef(props, "currentSegment");
const isOpen = ref(false);

const emit = defineEmits(["select"]);

const source = computed(() => {
  const raw = props.data;
  console.log("RAW ", raw);
  if (raw) {
    return raw.map((s) => ({
      ...s,
      row: s?.row ?? s?.seatCode?.match(/\d+/)?.[0] ?? "",
      column: s?.column ?? s?.seatCode?.match(/[A-Z]$/)?.[0] ?? "",
      type: (s?.type || "").toString().toUpperCase(),
    }));
  } else {
    return [];
  }
});

const allowed = computed(() => {
  const arr = Array.isArray(props.onlyType) ? props.onlyType : [props.onlyType];
  return new Set(arr.filter(Boolean).map((t) => t.toString().toUpperCase()));
});

const allCols = computed(() => {
  const set = new Set();
  for (const s of source.value) {
    if (!allowed.value.has(s.type)) continue;
    if (s.column) set.add(s.column);
  }
  return [...set].sort();
});

function splitForCount(n) {
  if (n <= 3) return [n];
  if (n === 4) return [2, "|", 2];
  if (n === 5) return [2, "|", 3];
  if (n === 6) return [3, "|", 3];
  if (n === 7) return [2, "|", 3, "|", 2];
  if (n === 8) return [2, "|", 4, "|", 2];
  if (n === 9) return [3, "|", 3, "|", 3];
  return [3, "|", 4, "|", 3];
}

const autoPattern = computed(() => {
  const cols = allCols.value;
  const split = splitForCount(cols.length);
  const tokens = [];
  let i = 0;
  for (const part of split) {
    if (part === "|") {
      tokens.push("|");
    } else {
      for (let k = 0; k < part && i < cols.length; k++) tokens.push(cols[i++]);
    }
  }
  return tokens;
});

const pattern = computed(() =>
  props.colsPattern && props.colsPattern.length
    ? props.colsPattern
    : autoPattern.value
);

const templateColumns = computed(() =>
  pattern.value.map((t) => (t === "|" ? "12px" : "max-content")).join(" ")
);

const rows = computed(() => {
  const grouped = new Map();
  console.log(source.value);
  for (const s of source.value) {
    if (!allowed.value.has(s.type)) continue;
    const r = Number(s.row) || 0;
    if (!grouped.has(r)) grouped.set(r, {});
    grouped.get(r)[s.column] = s;
  }
  return [...grouped.entries()]
    .sort((a, b) => a[0] - b[0])
    .map(([row, cols]) => ({ row, cols }));
});

const isSelected = (seat) => {
  if (!seat) return false;
  const original = currentSegmentRef.value?.seats?.[0]?.seatCode;
  const picked = currentSegmentRef.value?.newSeat?.seatCode;

  return picked ? seat.seatCode === picked : seat.seatCode === original;
};

function seatClasses(seat) {
  const base =
    "w-[25px] h-[25px] sm:w-[40px] sm:h-[40px] lg:w-[50px] lg:h-[50px] rounded-full text-xs sm:text-sm font-medium " +
    "flex items-center justify-center border-2 transition-colors select-none";
  if (!seat) return `${base} border-transparent text-transparent`;

  if (isSelected(seat)) {
    return `${base} border-transparent text-white bg-amRoseMexican`;
  }

  if (seat.status === "UNAVAILABLE") {
    return `${base} border-gray-300 text-gray-300 cursor-not-allowed bg-white`;
  }

  return `${base} border-amGreen text-amGreen bg-white hover:bg-amGreen hover:text-white cursor-pointer`;
}

const seatLabelDesktop = (seat) =>
  isSelected(seat) ? props.initials : seat?.seatCode ?? "";
const seatLabelMobile = (seat) =>
  isSelected(seat) ? props.initials : seat?.column ?? "";

function onSeatClick(seat) {
  if (!seat || seat.status === "UNAVAILABLE") return;
  emit("select", seat);
}
</script>

<template>
  <div
    class="relative rounded-t-full max-w-[320px] md:max-w-full w-full sm:w-[360px] md:w-[380px] border-[3.5px] bg-white p-1 h-full pt-[273px] pb-40 shadow-lg"
  >
    <header class="relative text-center border-t pb-10">
      <section
        v-show="isOpen"
        class="absolute text-left -top-[150px] right-[20%] border rounded bg-white p-5 md:py-[30px] flex justify-between z-50"
      >
        <div class="flex flex-col gap-4">
          <div
            class="flex text-xs items-center gap-[7px]"
            v-for="(item, index) in characteristics"
            :key="index"
          >
            <p>{{ item }}</p>
          </div>
        </div>
        <img :src="seat" alt="seat" width="91" class="hidden md:block" />
      </section>
      <div
        class="w-fit font-GarnettSemibold text-amGreen mx-auto px-1 bg-white font-semibold -translate-y-3 flex gap-1"
      >
        {{ title }}
        <button @mouseenter="isOpen = true" @mouseleave="isOpen = false">
          <Icon class="sm:hidden" name="Info" />
        </button>
      </div>
      <p class="text-xs text-gray-500 mt-1 -translate-y-3">{{ subtitle }}</p>
    </header>

    <div class="p-1 space-y-3">
      <div
        v-for="r in rows"
        :key="`d-${r.row}`"
        class="hidden sm:grid gap-y-3 justify-evenly"
        :style="{ gridTemplateColumns: templateColumns }"
      >
        <template v-for="(c, i) in pattern" :key="`d-${r.row}-${c}-${i}`">
          <div v-if="c === '|'" class="pointer-events-none" />
          <div
            v-else
            :class="seatClasses(r.cols[c])"
            @click="onSeatClick(r.cols[c])"
          >
            {{ seatLabelDesktop(r.cols[c]) }}
          </div>
        </template>
      </div>

      <div
        v-for="r in rows"
        :key="`m-${r.row}`"
        class="sm:hidden flex flex-col gap-2 border-b pb-4"
      >
        <div class="text-amGreen pl-5">{{ r.row }}</div>
        <div
          class="grid gap-y-2 justify-evenly"
          :style="{ gridTemplateColumns: templateColumns }"
        >
          <template v-for="(c, i) in pattern" :key="`m-${r.row}-${c}-${i}`">
            <div v-if="c === '|'" class="pointer-events-none" />
            <div
              v-else
              :class="seatClasses(r.cols[c])"
              @click="onSeatClick(r.cols[c])"
            >
              {{ seatLabelMobile(r.cols[c]) }}
            </div>
          </template>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.fuselaje {
  -webkit-mask-image: url("data:image/svg+xml;base64,PHN2ZyB4bWxucz0naHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmcnIHZpZXdCb3g9JzAgMCAzODEgMTMyNicgcHJlc2VydmVBc3BlY3RSYXRpbz0nbm9uZSc+PHBhdGggZmlsbD0nd2hpdGUnIGQ9J00xODkuMDQ5IC0zOC4yNDlDMTYxLjk5NCAtMzguMjQ5IDEzNi43NjggLTI2LjA0ODMgMTE0LjA3NiAtNS45NjQ4NEM5MS4zODAxIDE0LjEyMjMgNzEuMzE4IDQyLjAxMzUgNTQuNjUxNCA3My4xNDc1QzIxLjMwMjggMTM1LjQ0NCAxLjc1MSAyMTAuMzM2IDEuNzUwOTggMjYwLjY5NUwxLjc1MDk4IDEzMjYuNTlDMS43NTA5OCAxMzkwLjkyIDI3LjQ3MzYgMTQ2My4yMiA2Mi42MzE4IDE1MTkuNDZDODAuMjA0MSAxNTQ3LjU3IDEwMC4wODggMTU3MS41OCAxMjAuMjA4IDE1ODguNTZDMTQwLjM2NCAxNjA1LjU2IDE2MC41NjQgMTYxNS4zMiAxNzguODAyIDE2MTUuMzJIMjAxLjg4MkMyMjAuMTIgMTYxNS4zMiAyNDAuMzIgMTYwNS41NiAyNjAuNDc2IDE1ODguNTZDMjgwLjU5NiAxNTcxLjU4IDMwMC40OCAxNTQ3LjU3IDMxOC4wNTIgMTUxOS40NkMzNTMuMjEgMTQ2My4yMiAzNzguOTMzIDEzOTAuOTIgMzc4LjkzMyAxMzI2LjU5TDM3OC45MzMgMjYwLjY5NUMzNzguOTMzIDIxMC4zMzYgMzU5LjM4MSAxMzUuNDQ0IDMyNi4wMzIgNzMuMTQ3NUMzMDkuMzY2IDQyLjAxMzUgMjg5LjMwMyAxNC4xMjIzIDI2Ni42MDcgLTUuOTY0ODRDMjQ0LjI3IC0yNS43MzQ2IDIxOS40NzggLTM3Ljg2NjEgMTkyLjkwMSAtMzguMjQwMkwxOTEuNjM1IC0zOC4yNDlIMTg5LjA0OVonLz48L3N2Zz4=");
  mask-image: url("data:image/svg+xml;base64,PHN2ZyB4bWxucz0naHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmcnIHZpZXdCb3g9JzAgMCAzODEgMTMyNicgcHJlc2VydmVBc3BlY3RSYXRpbz0nbm9uZSc+PHBhdGggZmlsbD0nd2hpdGUnIGQ9J00xODkuMDQ5IC0zOC4yNDlDMTYxLjk5NCAtMzguMjQ5IDEzNi43NjggLTI2LjA0ODMgMTE0LjA3NiAtNS45NjQ4NEM5MS4zODAxIDE0LjEyMjMgNzEuMzE4IDQyLjAxMzUgNTQuNjUxNCA3My4xNDc1QzIxLjMwMjggMTM1LjQ0NCAxLjc1MSAyMTAuMzM2IDEuNzUwOTggMjYwLjY5NUwxLjc1MDk4IDEzMjYuNTlDMS43NTA5OCAxMzkwLjkyIDI3LjQ3MzYgMTQ2My4yMiA2Mi42MzE4IDE1MTkuNDZDODAuMjA0MSAxNTQ3LjU3IDEwMC4wODggMTU3MS41OCAxMjAuMjA4IDE1ODguNTZDMTQwLjM2NCAxNjA1LjU2IDE2MC41NjQgMTYxNS4zMiAxNzguODAyIDE2MTUuMzJIMjAxLjg4MkMyMjAuMTIgMTYxNS4zMiAyNDAuMzIgMTYwNS41NiAyNjAuNDc2IDE1ODguNTZDMjgwLjU5NiAxNTcxLjU4IDMwMC40OCAxNTQ3LjU3IDMxOC4wNTIgMTUxOS40NkMzNTMuMjEgMTQ2My4yMiAzNzguOTMzIDEzOTAuOTIgMzc4LjkzMyAxMzI2LjU5TDM3OC45MzMgMjYwLjY5NUMzNzguOTMzIDIxMC4zMzYgMzU5LjM4MSAxMzUuNDQ0IDMyNi4wMzIgNzMuMTQ3NUMzMDkuMzY2IDQyLjAxMzUgMjg5LjMwMyAxNC4xMjIzIDI2Ni42MDcgLTUuOTY0ODRDMjQ0LjI3IC0yNS43MzQ2IDIxOS40NzggLTM3Ljg2NjEgMTkyLjkwMSAtMzguMjQwMkwxOTEuNjM1IC0zOC4yNDlIMTg5LjA0OVonLz48L3N2Zz4=");
  -webkit-mask-size: 100% 100%;
  mask-size: 100% 100%;
  -webkit-mask-repeat: no-repeat;
  mask-repeat: no-repeat;
  -webkit-mask-position: center;
  mask-position: center;
  border: solid 4px #f3f3f5;
}
</style>
