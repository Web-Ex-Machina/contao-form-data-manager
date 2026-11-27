(() => {
    const INTERACTION_EVENTS = ['keydown', 'keyup', 'click'];

    const fillFirstAppearanceField = (form) => {
        const field = form.querySelector('[name="fdm[first_appearance]"]');

        if (field) {
            field.value = Date.now();
        }
    };

    const onFirstInteraction = (event) => {
        const form = event.currentTarget;
        const field = form.querySelector('[name="fdm[first_interaction]"]');

        if (field && 0 === field.value.length) {
            field.value = Date.now();
        }

        INTERACTION_EVENTS.forEach((type) => form.removeEventListener(type, onFirstInteraction));
    };

    const manageForm = (form) => {
        if ('true' === form.dataset.fdm || !form.querySelector('[name="fdm[first_appearance]"]')) {
            return;
        }

        fillFirstAppearanceField(form);
        INTERACTION_EVENTS.forEach((type) => form.addEventListener(type, onFirstInteraction));
        form.dataset.fdm = 'true';
    };

    const init = () => {
        // Forms displayed in a modal are only managed once the modal is opened
        document.querySelectorAll('form').forEach((form) => {
            if (!form.closest('.modalFW')) {
                manageForm(form);
            }
        });

        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                if ('class' === mutation.attributeName && mutation.target.classList.contains('active')) {
                    mutation.target.querySelectorAll('form').forEach(manageForm);
                }
            });
        });

        document.querySelectorAll('.modalFW').forEach((modal) => {
            observer.observe(modal, { attributes: true, attributeFilter: ['class'] });
        });
    };

    if ('loading' === document.readyState) {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
