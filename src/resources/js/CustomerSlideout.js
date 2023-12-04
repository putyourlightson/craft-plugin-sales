/** global: Craft */
/** global: Garnish */
Craft.CustomerSlideout = Craft.CpScreenSlideout.extend(
    {
        closeMeMaybe: function() {
            this.close();
        },

        close: function() {
            this.base();

            // There can be only one!
            this.destroy();
        },
    });
