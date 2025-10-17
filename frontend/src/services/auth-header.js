export default function authHeader() {
  let token = localStorage.getItem("token");

  if (token) {
    return {
      Authorization: "Bearer " + token,
      "Content-Type": "application/vnd.api+json",
      "Accept": "application/vnd.api+json",
    };
  } else {
    return {};
  }
}
