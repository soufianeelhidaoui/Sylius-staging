export const Mixins = {
    methods: {
        resize(image, size){
            try {
                console.log(image)
                if( image.indexOf('placeholder') >= 0 ){
                    return 'https://via.placeholder.com/'+size+'/dee6ec/cbd8e1';
                }
                else if(size === 'original') {
                    return image;
                }
                else {
                    let matches = image.match(/(.*\/[\w\-\_\.]+)\.(\w{2,4})/);
                    return matches[1] + '_' + size + '_crop_center.' + matches[2];
                }
            } catch (e) { return image }
        },
        handleize(value) {

            if( !value || !value.length )
                return value;

            let str = value.replace(/^\s+|\s+$/g, ''); // trim
            str = str.toLowerCase();

            // remove accents, swap ñ for n, etc
            let from = "àáäâèéëêìíïîòóöôùúüûñç·/_,:;";
            let to   = "aaaaeeeeiiiioooouuuunc------";
            for (let i=0, l=from.length ; i<l ; i++) {
                str = str.replace(new RegExp(from.charAt(i), 'g'), to.charAt(i));
            }

            str = str.replace(/[^a-z0-9 -]/g, '') // remove invalid chars
                .replace(/\s+/g, '-') // collapse whitespace and replace by -
                .replace(/-+/g, '-'); // collapse dashes

            return str;
        },
        translate(data){

            let data_key = this.handleize(data);

            if( window.shop.translations && typeof window.shop.translations[data_key] != 'undefined' )
                return window.shop.translations[data_key]

            return data;
        },
        formatMoney(cents, format) {
            if (typeof cents == 'string') { cents = cents.replace('.',''); }
            let value = '';
            let placeholderRegex = /\{\{\s*(\w+)\s*\}\}/;
            let formatString = (format || window.shop.moneyFormat);

            function defaultOption(opt, def) {
                return (typeof opt == 'undefined' ? def : opt);
            }

            function formatWithDelimiters(number, precision, thousands, decimal) {
                precision = defaultOption(precision, 2);
                thousands = defaultOption(thousands, ',');
                decimal   = defaultOption(decimal, '.');

                if (isNaN(number) || number == null) { return 0; }

                number = (number/100.0).toFixed(precision);

                let parts   = number.split('.'),
                    dollars = parts[0].replace(/(\d)(?=(\d\d\d)+(?!\d))/g, '$1' + thousands),
                    cents   = parts[1] ? (decimal + parts[1]) : '';

                return dollars + cents;
            }

            switch(formatString.match(placeholderRegex)[1]) {
                case 'amount':
                    value = formatWithDelimiters(cents, 2);
                    break;
                case 'amount_no_decimals':
                    value = formatWithDelimiters(cents, 0);
                    break;
                case 'amount_with_comma_separator':
                    value = formatWithDelimiters(cents, 2, '.', ',');
                    break;
                case 'amount_no_decimals_with_comma_separator':
                    value = formatWithDelimiters(cents, 0, '.', ',');
                    break;
            }

            return formatString.replace(placeholderRegex, value);
        },
        replacestring (st, rep, repWith) {
          const result = st.split(rep).join(repWith)
          return result;
        }
    }
};
