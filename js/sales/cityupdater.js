CityUpdater = Class.create();
CityUpdater.prototype = {
    initialize: function (countryEl, regionEl, cityTextEl, citySelectEl, cities) {
        this.regionEl = $(regionEl);
        this.cityTextEl = $(cityTextEl);
        this.citySelectEl = $(citySelectEl);
        this.cities = cities;
        this.countryEl = $(countryEl);
        if (this.citySelectEl.options.length <= 1) {
            this.update();
        }

        Event.observe(this.regionEl, 'change', this.update.bind(this));
        Event.observe(this.countryEl, 'change', this.update.bind(this));
        Event.observe(this.citySelectEl, 'change', this.updateCity.bind(this));
    },

    update: function () {
        if (this.cities[this.regionEl.value]) {
            var i, option, city, def;
            def = this.citySelectEl.getAttribute('defaultValue');
            if (this.cityTextEl) {
                if (!def) {
                    def = this.cityTextEl.value.toLowerCase();
                }
            }

            this.citySelectEl.options.length = 1;
            for (cityId in this.cities[this.regionEl.value]) {
                city = this.cities[this.regionEl.value][cityId];

                option = document.createElement('OPTION');
                option.value = city.name;
                option.text = city.name.stripTags();
                option.title = city.name;

                if (this.citySelectEl.options.add) {
                    this.citySelectEl.options.add(option);
                } else {
                    this.citySelectEl.appendChild(option);
                }

                if (cityId == def || (city.name && city.name == def) ||
                    (city.name && city.code.toLowerCase() == def)
                ) {
                    this.citySelectEl.value = city.name;
                }
            }

            if (this.cityTextEl) {
                this.cityTextEl.style.display = 'none';
            }
            this.citySelectEl.style.display = '';
        }
        else {
            this.citySelectEl.options.length = 1;
            if (this.cityTextEl) {
                this.cityTextEl.style.display = '';
            }
            this.citySelectEl.style.display = 'none';
            Validation.reset(this.citySelectEl);
        }
    },

    updateCity: function () {
        var sIndex = this.citySelectEl.selectedIndex;
        this.cityTextEl.value = this.citySelectEl.options[sIndex].value;
    }
}