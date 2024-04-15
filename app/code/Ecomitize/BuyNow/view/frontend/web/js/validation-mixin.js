define([
    'jquery',
    'underscore'
], function ($, _) {
    "use strict";

    return function () {
        $.validator.addMethod(
            'validate-phone',
            function (v) {
                return _.isEmpty(v) || /^(\+(38)\s)?(\d{3})?-?\s?(\d{3}[-. ]?\d{2}[-. ]?\d{2})$/.test(v);
            },
            $.mage.__('Please enter a valid phone number. For example +38 0ХХ-ХХХ-ХХ-ХХ.')
        );
    }
});
