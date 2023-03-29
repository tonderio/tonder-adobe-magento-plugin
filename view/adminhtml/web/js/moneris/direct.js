define([
    'jquery',
    'Tonder_Payment/js/tonder',
    'underscore'
], function ($, Tonder) {
    'use strict';

    return Tonder.extend({

        invalidFormValidate: function () {
            $('#' + self.code + '_cc_number').val('');
            $('#' + self.code + '_cc_cid').val('');

            return this;
        },

        beforeSubmit: function (event) {
            var form = $(event.target),
                ccNumberField = form.find('#' + this.code + '_cc_number_encrypt'),
                ccCidField = form.find('#' + this.code + '_cc_cid_encrypt'),
                ccNumber =  ccNumberField.val(),
                ccCid = ccCidField.val();

            form.find('#' + this.code + '_cc_number').val(ccNumber);
            form.find('#' + this.code + '_cc_cid').val(ccCid);

            ccNumberField.val('');
            ccCidField.val('');

            return this;
        }
    });
});
