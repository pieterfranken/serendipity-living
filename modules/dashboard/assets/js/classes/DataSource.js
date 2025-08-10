'use strict';

/**
 * Dashboard_Classes_DataSource
 */
class Dashboard_Classes_DataSource
{
    constructor(delegate) {
        Queue.configure(Promise);
        this.delegate = delegate;
        this.queue = new Queue(4, 10000);
        this.datasourceMetricCache = {};
    }

    loadData(dateRange, aggregationInterval, dimension, metrics, widgetConfig, resetCache, extraData, compare) {
        return this.queue.add(() => {
            return oc.ajax(this.delegate.getEventHandler('onGetWidgetData'), {
                progressBar: false,
                async: true,
                data: {
                    _dash_definition: this.delegate.getCurrentDashboard().code,
                    widget_config: widgetConfig,
                    date_start: dateRange.dateStart,
                    date_end: dateRange.dateEnd,
                    dimension: dimension,
                    metrics: metrics,
                    aggregation_interval: aggregationInterval,
                    reset_cache: resetCache,
                    compare: compare ?? '',
                    extra_data: extraData ?? {}
                }
            });
        });
    }

    loadCustomData(dateRange, aggregationInterval, widgetConfig, resetCache, extraData, compare) {
        return this.queue.add(() => {
            return oc.ajax(this.delegate.getEventHandler('onGetWidgetCustomData'), {
                progressBar: false,
                async: true,
                data: {
                    _dash_definition: this.delegate.getCurrentDashboard().code,
                    widget_config: widgetConfig,
                    date_start: dateRange.dateStart,
                    date_end: dateRange.dateEnd,
                    aggregation_interval: aggregationInterval,
                    reset_cache: resetCache,
                    compare: compare ?? '',
                    extra_data: extraData ?? {}
                }
            });
        });
    }

    loadStaticContent(dateRange, aggregationInterval, widgetConfig, resetCache, extraData, compare) {
        return this.queue.add(() => {
            return oc.ajax(this.delegate.getEventHandler('onGetWidgetStaticContent'), {
                progressBar: false,
                async: true,
                data: {
                    _dash_definition: this.delegate.getCurrentDashboard().code,
                    widget_config: widgetConfig,
                    date_start: dateRange.dateStart,
                    date_end: dateRange.dateEnd,
                    aggregation_interval: aggregationInterval,
                    reset_cache: resetCache,
                    compare: compare ?? '',
                    extra_data: extraData ?? {}
                }
            });
        });
    }

    runDataSourceHandler(handlerName, widgetConfig, extraData) {
        return this.queue.add(() => {
            return oc.ajax(this.delegate.getEventHandler('onRunDataSourceHandler'), {
                progressBar: true,
                async: true,
                data: Object.assign({}, {
                    _dash_definition: this.delegate.getCurrentDashboard().code,
                    handler: handlerName,
                    widget_config: widgetConfig
                }, extraData)
            });
        })
    }

    runCustomWidgetHandler(handlerName, widgetConfig, extraData) {
        return this.queue.add(() => {
            return oc.ajax(this.delegate.getEventHandler('onRunCustomWidgetHandler'), {
                progressBar: true,
                async: true,
                data: {
                    _dash_definition: this.delegate.getCurrentDashboard().code,
                    handler: handlerName,
                    widget_config: widgetConfig,
                    extra_data: extraData
                }
            });
        })
    }
}
