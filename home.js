document.addEventListener('DOMContentLoaded', function () {
    // =========================
    // HERO IMAGE ROTATION
    // =========================
    const heroSection = document.querySelector('.hero');
    const images = [
        'uploads/giphy.gif',
        'uploads/animatedgif.gif',
        'uploads/moving.gif',
        'uploads/giphy.gif'
    ];
    let currentIndex = 0;
    const intervalTime = 4000;

    function setHeroBackground(index) {
        const img = new Image();
        img.src = images[index];
        img.onload = () => {
            heroSection.style.backgroundImage = `url(${img.src})`;
        };
        img.onerror = () => {
            console.error(`Failed to load image: ${img.src}`);
        };
    }

    setHeroBackground(currentIndex);
    setInterval(() => {
        currentIndex = (currentIndex + 1) % images.length;
        setHeroBackground(currentIndex);
    }, intervalTime);

    // =========================
    // PRODUCT MODAL LOGIC
    // =========================
    const modal = document.getElementById("product-modal");
    const closeModal = document.querySelector(".close");

    if (closeModal && modal) {
        closeModal.addEventListener("click", () => {
            modal.style.display = "none";
        });
    }

    document.querySelectorAll(".view-product").forEach(button => {
        button.addEventListener("click", function (event) {
            event.stopPropagation();
            const card = this.closest(".product-card");
            if (!card) return;

            const productId = card.dataset.id;
            const productImage = card.dataset.image;
            const productName = card.dataset.name;
            const productPrice = card.dataset.price;
            const productDesc = card.dataset.description;

            document.getElementById("modal-image").src = productImage;
            document.getElementById("modal-name").textContent = productName;
            document.getElementById("modal-price").textContent = `₱${productPrice}`;
            document.getElementById("modal-description").textContent = productDesc;
            document.getElementById("modal-add-to-cart").value = productId;

            // Set customization options
            const bouquetSize = card.dataset.bouquetSize || 'Standard';
            const ribbonColor = card.dataset.ribbonColor || 'Red';
            const addOns = card.dataset.addOns || 'None';

            document.getElementById("modal-bouquet-size").textContent = bouquetSize;
            document.getElementById("modal-ribbon-color").textContent = ribbonColor;
            document.getElementById("modal-add-ons").textContent = addOns;

            modal.style.display = "flex";
        });
    });

    window.addEventListener("click", (e) => {
        if (e.target === modal) {
            modal.style.display = "none";
        }
    });

    // =========================
    // LOGIN CHECK FUNCTION
    // =========================
    function checkLoginAndShowModal() {
        if (typeof isLoggedIn !== "undefined" && !isLoggedIn) {
            const loginModal = document.getElementById('loginModal');
            if (loginModal) loginModal.style.display = 'flex';
            return false;
        }
        return true;
    }

    // =========================
    // INTERCEPT FORMS & LINKS
    // =========================
    document.querySelectorAll('.product-buttons form').forEach(form => {
        form.addEventListener('submit', function (event) {
            if (!checkLoginAndShowModal()) {
                event.preventDefault();
            }
        });
    });

    document.querySelectorAll('.product-buttons a[href="cart.php"], .product-buttons a[href="checkout.php"]').forEach(link => {
        link.addEventListener('click', function (event) {
            event.preventDefault();
            checkLoginAndShowModal();
        });
    });

    document.querySelectorAll('#modal-actions a[href="checkout.php"]').forEach(link => {
        link.addEventListener('click', function (event) {
            event.preventDefault();
            checkLoginAndShowModal();
        });
    });

    document.querySelectorAll('.customization-form').forEach(form => {
        form.addEventListener('submit', function (event) {
            if (!checkLoginAndShowModal()) {
                event.preventDefault();
            }
        });
    });

    // =========================
    // OPTIONAL: AUTO-LOGIN PROMPT
    // =========================
    let modalShown = false;
    function showLoginModalOnPageLoad() {
        if (!modalShown && typeof isLoggedIn !== "undefined" && !isLoggedIn) {
            const loginModal = document.getElementById("loginModal");
            if (loginModal) loginModal.style.display = "flex";
            modalShown = true;
        }
    }

    // Show login modal on scroll (optional)
    // window.addEventListener("scroll", debounce(showLoginModalOnPageLoad, 1000));

    // Show login modal after delay (optional)
    // setTimeout(showLoginModalOnPageLoad, 5000);

    // Debounce helper (optional use)
    function debounce(func, delay) {
        let timeout;
        return function () {
            clearTimeout(timeout);
            timeout = setTimeout(func, delay);
        };
    }
});
