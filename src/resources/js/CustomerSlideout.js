/** global: Craft */
/** global: Garnish */
PluginSales.CustomerSlideout = Craft.CpScreenSlideout.extend(
    {
        init: function(customer) {
            const action = 'plugin-sales/slideout/render?customer=' + encodeURIComponent(customer);

            this.on('load', () => {
                this.removeNamespace();
                this.processHtmx();
            });

            this.base(action);

            this.$saveBtn.remove();
            this.$cancelBtn.text(Craft.t('app', 'Close'));
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

        processHtmx: function() {
            htmx.process(this.$container[0]);
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
