export function registerPackageComponents() {
  const components = import.meta.glob("./Pages/**/*.vue", { eager: true });

  const registeredComponents = {};

  for (const path in components) {
    const component = components[path];
    const componentName = path
      .replace("./Pages/", "")
      .replace(".vue", "")
      .replace(/\//g, "/");

    console.log("componentName", componentName);

    registeredComponents[`Amx/CorporatePriority/${componentName}`] =
      component.default;
  }

  return registeredComponents;
}
