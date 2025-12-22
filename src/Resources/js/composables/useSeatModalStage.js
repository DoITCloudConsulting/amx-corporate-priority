import { ref, watch } from "vue";
import { corporatePriorityService } from "../../services/CorporatePriorityService";

export const useSeatModalStage = ({ trads, close }) => {
  const stageName = ref("");

  const setStageName = (newStage) => (stageName.value = newStage);

  const createStage = ({ stageProps = {}, t }) => ({
    title: t.title ?? trads.label_seats,
    mainText: t.mainText,
    backButton: {
      action: () => close(),
    },
    checkbox: {
      agreeTerms: false,
      text: trads.label_agree_terms,
    },
    primaryButton: {
      text: trads.label_continue,
      event: () => {},
    },
    ...stageProps,
  });

  const stages = {
    preferent: createStage({
      t: {
        mainText: trads.label_seat_is_preferent,
      },
      stageProps: {
        backButton: {
          action: async () => {
            const payload = corporatePriorityService.prepareCasePayload({
              case: {
                status: "Cancelado",
              },
            });
            close();
            await corporatePriorityService.createCase(payload);
          },
        },
      },
    }),
    noPreferent: createStage({
      t: {
        mainText: trads.label_seat_no_preferent,
      },
      stageProps: {
        backButton: {
          action: async () => {
            const payload = corporatePriorityService.prepareCasePayload({
              case: {
                status: "Cancelado",
              },
            });
            close();
            await corporatePriorityService.createCase(payload);
          },
        },
      },
    }),
    condonate: createStage({
      t: {
        title: trads.label_condonate,
        mainText: trads.label_seat_condonate,
      },
      stageProps: {
        checkbox: null,
        backButton: false,
        primaryButton: {
          text: trads.label_seat_yes_condonate,
          event: () => {},
        },
        secondaryButton: {
          text: trads.label_seat_no_condonate,
          event: () => {},
        },
      },
    }),
    success: createStage({
      t: {
        mainText: trads.label_change_success,
      },
    }),
  };

  console.log(stages);

  const current = ref({});

  watch(stageName, (newValue) => {
    if (stages[newValue] === undefined) {
      console.warn("⚙️ Usa un stageName correcto en useSeatModalStage");
      current.value = {};
      return;
    }

    current.value = stages[newValue];
  });

  return { stageName, setStageName, current };
};
