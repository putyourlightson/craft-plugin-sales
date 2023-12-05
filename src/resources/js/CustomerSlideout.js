/** global: Craft */
/** global: Garnish */
PluginSales.CustomerSlideout = Craft.CpScreenSlideout.extend(
    {
        init: function(customer) {
            const action = 'plugin-sales/slideout/render?customer=' + encodeURIComponent(customer);
            this.base(action);

            this.open();
            this.$saveBtn.remove();
            this.$cancelBtn.text(Craft.t('app', 'Close'));

            this.on('load', () => {
                this.removeNamespace();
                this.removeFocus();
                htmx.process(this.$container[0]);
            });
        },

        removeNamespace: function() {
            const namespace = this.namespace;
            this.$container.find('[id]').each(function() {
                this.id = this.id.replace(namespace + '-', '');
            });
            this.$container.find('[name]').each(function() {
                this.name = this.name.replace(namespace + '[', '');
                this.name = this.name.replace(']', '');
            });
        },

        removeFocus: function() {
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
        },

        handleSubmit: function(event) {
            event.preventDefault();
        },

        closeMeMaybe: function() {
            this.close();
        },

        close: function() {
            this.base();

            // There can be only one!
            this.destroy();
        },
    });
