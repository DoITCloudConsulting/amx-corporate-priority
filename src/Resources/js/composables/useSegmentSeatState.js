import { reactive, computed } from "vue";

export function useSegmentSeatState(currentSegment) {
  // estado interno
  const agreeTermsBySegment = reactive({});
  const seatAssignedBySegment = reactive({});

  // inicializadores (idénticos a los actuales)
  const initAgreeTerms = (segments = []) => {
    segments.forEach((_, idx) => {
      if (agreeTermsBySegment[idx] === undefined) {
        agreeTermsBySegment[idx] = false;
      }
    });
  };

  const initSeatAssigned = (segments = []) => {
    segments.forEach((_, idx) => {
      if (seatAssignedBySegment[idx] === undefined) {
        seatAssignedBySegment[idx] = false;
      }
    });
  };

  // computed por segmento actual
  const agreeTermsForCurrentSegment = computed({
    get() {
      return agreeTermsBySegment[currentSegment.value] ?? false;
    },
    set(value) {
      agreeTermsBySegment[currentSegment.value] = value;
    },
  });

  const assignedSeatForCurrentSegment = computed({
    get() {
      return seatAssignedBySegment[currentSegment.value] ?? false;
    },
    set(value) {
      seatAssignedBySegment[currentSegment.value] = value;
    },
  });

  return {
    // estado crudo (para watchers externos)
    agreeTermsBySegment,
    seatAssignedBySegment,

    // inicializadores
    initAgreeTerms,
    initSeatAssigned,

    // API de alto nivel
    agreeTermsForCurrentSegment,
    assignedSeatForCurrentSegment,
  };
}
