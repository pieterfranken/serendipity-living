Vue.component('dashboard-component-dashboard-widget-static', {
    extends: Vue.options.components['dashboard-component-dashboard-widget-base'],
    data: function () {
        return {
        }
    },
    computed: {
        loadedValue: function () {
            return this.fullWidgetData ? this.fullWidgetData : undefined;
        },
    },
    methods: {
        getRequestDimension: function () {
            return 'none';
        },

        getRequestMetrics: function () {
            return [];
        },

        useCustomData: function () {
            return true;
        },

        makeDefaultConfigAndData: function () {
            Vue.set(this.widget.configuration, 'title', 'My Custom Widget');
        },

        getSettingsConfiguration: function () {
            return this.loadedValue && this.loadedValue.properties;
        }
    },
    template: '#dashboard_vuecomponents_dashboard_widget_static'
});
