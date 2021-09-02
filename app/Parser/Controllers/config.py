class Config(object):
    def siteData(self):
        return {
            'basicdecor': {
                'tab': {
                    'div': 'product-summary__tabs-titles simple-tabs__title-panel js-product-tabs d-none d-block--lg',
                    'tab': 'd-inline-block h-v-align--m',
                    'tab-index': 'data-tab-content-index',
                    'link': 'product-card__img-box-link',
                },
                'Articul': {
                    'list': 'product-spec-list dotted-list',
                },
                'Params': {
                    'params_group': 'detail-spec__group',
                    'div_delete': 'detail-spec__hint d-inline--block',
                    'div': 'product-summary__spec-wrap',
                    'div_value': 'product-summary__spec-name product-summary__spec-name--x-size',
                    'div_value_desc': 'product-summary__spec-name product-summary__spec-name--x-size is-has-description',
                },
                'IsAvailable': {
                    'div': 'icons icons--not-produce availability',
                },
                'Price': {
                    'current': 'pr-sum__price',
                    'action': 'price__old product-summary__price-old',
                },
                'Quantity': {
                    'div': 'p25-l p25-r p30-b product-summary__offer-container-inner h-brd h-brd--all',
                    'available': 'icons icons--many availability',
                    'l_available': 'icons icons--little availability',
                    'u_available': 'icons icons--under-order availability',
                },
                'Image': {
                    'div': 'col col--xs-auto product-summary__col-1 product-summary__gallery',
                },
                '403': 'cf-wrapper cf-header cf-error-overview'
            }
        }
