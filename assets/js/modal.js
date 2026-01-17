// Форма xthtp 30 сек после открытия страницы
function setCookie(cname, cvalue, exdays, path) {
    var d = new Date();
    d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
    var expires = "expires=" + d.toUTCString();
    document.cookie = cname + "=" + cvalue + ";" + expires + ";path=" + path;
}

function getCookie(cname) {
    const name = cname + "=";
    const decodedCookie = decodeURIComponent(document.cookie);
    const cookies = decodedCookie.split(';');
    for (let i = 0; i < cookies.length; i++) {
        let cookie = cookies[i];
        while (cookie.charAt(0) == ' ') {
            cookie = cookie.substring(1);
        }
        if (cookie.indexOf(name) === 0) {
            return cookie.substring(name.length, cookie.length);
        }
    }
    return null;
}

const setCookieButton = document.querySelector("#setCookieRegion");
if (setCookieButton) {
    setCookieButton.addEventListener("click", function() {
        document.querySelector("#modals-region").style.display = "none";
    });
}

const modalsMoskow = document.querySelector("#modals-region");
if (modalsMoskow) {
    modalsMoskow.addEventListener("click", function(event) {
        if (event.target === modalsMoskow) {
            modalsMoskow.style.display = "none";
        }
    });

    const modalsContent = document.querySelector(".modals__content.modals__region");
    if (modalsContent) {
        modalsContent.addEventListener("click", function(event) {
            event.stopPropagation();
        });
    }
}

const popupCookie = getCookie("popup-reg");
if (popupCookie === null) {
    setTimeout(function() {
        modalsMoskow.style.display = "block";
        document.querySelector(".modals__region").style.opacity = 1;
        setCookie("popup-reg", "shown", 1, '/');
    }, 5000);
} else {
    modalsMoskow.style.display = "none";
    document.querySelector(".modals__region").style.opacity = 0;
}



// Modal


const modalBtn = document.querySelectorAll('[datamodals]');
const body = document.body;
const modalClose = document.querySelectorAll('.modals__close');
const modal = document.querySelectorAll('.modals');


modalBtn.forEach(item => {
    item.addEventListener('click', event => {
        event.preventDefault();
        let $this = event.currentTarget;
        let modalId = $this.getAttribute('datamodals');
        let modal = document.getElementById(modalId);
        let modalContent = modal.querySelector('.modals__content');

        modalContent.addEventListener('click', event => {
            event.stopPropagation();
        });

        modal.classList.add('show-modals');
        body.classList.add('no-scroll');

        setTimeout(() => {
            modalContent.style.transform = 'none';
            modalContent.style.opacity = '1';
        }, 1);

    });
});


modalClose.forEach(item => {
    item.addEventListener('click', event => {
        event.preventDefault();
        let currentModal = event.currentTarget.closest('.modals');

        closeModal(currentModal);
    });
});


modal.forEach(item => {
    item.addEventListener('click', event => {
        let currentModal = event.currentTarget;

        closeModal(currentModal);
    });
});


function closeModal(currentModal) {
    // let modalContent = currentModal.querySelector('.modals__content');
    // if (currentModal.id !== 'modals-zayvka-popup') {
    //     modalContent.removeAttribute('style');
    // }

    setTimeout(() => {
        currentModal.classList.remove('show-modals');
        body.classList.remove('no-scroll');
    }, 200);
}