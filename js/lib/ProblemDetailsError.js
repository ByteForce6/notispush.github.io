export class ProblemDetailsError extends Error {
  /**
   * Detalle de los errores devueltos por un servicio.
   * Crea una instancia de ProblemDetailsError.
   * @param {object} problemDetails Objeto con la descripcipon del error.
   */
  constructor(problemDetails) {
    // @ts-ignore
    super(
      typeof problemDetails["detail"] === "string"
        ? // @ts-ignore
          problemDetails["detail"]
        : // @ts-ignore
          typeof problemDetails["title"] === "string"
          ? // @ts-ignore
            problemDetails["title"]
          : "Error",
    );

    this.problemDetails = problemDetails;
  }
}
