document.addEventListener("DOMContentLoaded", function () {
    
    // MOBILE SIDEBAR TOGGLE

    const sidebar = document.querySelector(".sidebar");
    const toggleBtn = document.querySelector(".toggle-btn");

    if (toggleBtn) {
        toggleBtn.addEventListener("click", function () {
            sidebar.classList.toggle("active");
        });
    }

    // Close sidebar when clicking outside (mobile)
    document.addEventListener("click", function (e) {
        if (
            sidebar &&
            toggleBtn &&
            !sidebar.contains(e.target) &&
            !toggleBtn.contains(e.target)
        ) {
            sidebar.classList.remove("active");
        }
    });

    // AUTO ACTIVE MENU

    let currentPage = window.location.pathname.split("/").pop();

    document.querySelectorAll(".sidebar a").forEach(link => {
        let href = link.getAttribute("href");

        if (href === currentPage) {
            link.classList.add("active");
        }
    });

});