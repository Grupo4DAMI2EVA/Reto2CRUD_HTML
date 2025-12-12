document.addEventListener("DOMContentLoaded", async () => {
  const addGameBtn = document.getElementById("addGameBtn");

  addGameBtn.onclick = function () {
    window.location.href = "addGames.html";
  };
});
