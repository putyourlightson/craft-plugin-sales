function openCustomerSlideout(email) {
    const action = 'plugin-sales/slideout/render?email=' + encodeURIComponent(email);
    const slideout = new Craft.CustomerSlideout(action, null);
    slideout.namespace = null;
    slideout.open();
    slideout.$saveBtn.remove();
    slideout.$cancelBtn.text(Craft.t('app', 'Close'));
    slideout.on('load', () => {
        removeNamespace(slideout);
        removeFocus();
        htmx.process(slideout.$container[0]);
    });
}

function removeNamespace(slideout) {
    slideout.$container.find('[id]').each(function() {
        this.id = this.id.replace(slideout.namespace + '-', '');
    });
    slideout.$container.find('[name]').each(function() {
        this.name = this.name.replace(slideout.namespace + '[', '');
        this.name = this.name.replace(']', '');
    });
}

function removeFocus() {
    // Defer removing the focus for at least two timeouts, to be sure!
    setTimeout(() => {
        document.activeElement.blur();
    }, 100);
    setTimeout(() => {
        document.activeElement.blur();
    }, 300);
    setTimeout(() => {
        document.activeElement.blur();
    }, 500);
}
