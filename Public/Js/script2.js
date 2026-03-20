document.addEventListener("DOMContentLoaded", () => {
    const toggleBar = document.getElementById("toggle-bar");
    const sidebar = document.querySelector(".sidebar");
    const mainContent = document.querySelector(".main-content");

    if (toggleBar) {
        toggleBar.addEventListener("click", () => {
            sidebar.classList.toggle("mini-sidebar");
            mainContent.classList.toggle("mini-sidebar");
        });
    }

    const logoutButton = document.getElementById("logout-button");
    if (logoutButton) {
        logoutButton.addEventListener("click", () => {
            document.forms[0].submit();
        });
    }
});