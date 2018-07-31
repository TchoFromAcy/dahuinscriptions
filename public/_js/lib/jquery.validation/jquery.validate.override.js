$.validator.addMethod("regexp", function(value, element, param) {
    return this.optional(element) || param.test(value);
});

$.extend($.validator.messages, {
    required: "Ce champ est obligatoire.",
    remote: "Please fix this field.",
    email: "Cet email est incorrect.",
    url: "L'url saisie est incorrecte."
   

});

$.extend($.validator.prototype, {showLabel: function(element, message) {

	
        var label = this.errorsFor(element), forName=$(element).attr('name');
        
       forName=forName.replace(/[\[\]']+/g,'');
       
       console.log(forName);
        
        if (label.length) {
            // refresh error/success class
            label.removeClass(this.settings.validClass).addClass(this.settings.errorClass);

            // check if we have a generated label, replace the message then
            if (label.attr("generated")) {

                label.attr('data-error', message).html('<em class="fa fa-exclamation-triangle"></em>');
                
                $('.help-block[data-for='+forName+']').html(message);
            }
        } else {
            // create label
        	
        	
        	
        	
        	
            label = $("<" + this.settings.errorElement + "/>")
                    .attr({"for": this.idOrName(element), id: 'labelError_' + this.idOrName(element), generated: true})
                    .addClass(this.settings.errorClass+' input-group-addon')
                    .attr('data-error', message || "").html('<em class="fa fa-exclamation-triangle"></em>');
            if (this.settings.wrapper) {
                // make sure the element is visible, even in IE
                // actually showing the wrapped element is handled elsewhere
                label = label.hide().show().wrap("<" + this.settings.wrapper + "/>").parent();
            }
            if (!this.labelContainer.append(label).length) {
                if (this.settings.errorPlacement) {
                    this.settings.errorPlacement(label, $(element));
                } else {
                    label.insertAfter(element);
                }
            }
            
            $('.help-block[data-for='+forName+']').html(message);
        }
        if (!message && this.settings.success) {
            label.text("");
            if (typeof this.settings.success === "string") {
                label.addClass(this.settings.success);
            } else {
                this.settings.success(label, element);
            }
        }
        this.toShow = this.toShow.add(label);
    }


});

jQuery.validator.addMethod("require_from_group", function(value, element, options) {
    var numberRequired = options[0];
    var selector = options[1];
    var fields = $(selector, element.form);
    var filled_fields = fields.filter(function() {
        // it's more clear to compare with empty string
        return $(this).val() != "";
    });
    var empty_fields = fields.not(filled_fields);
    // we will mark only first empty field as invalid
    if (filled_fields.length < numberRequired && empty_fields[0] == element) {
        return false;
    }
    return true;
    // {0} below is the 0th item in the options field
}, $.validator.format("'Please enter either username/ email address to recover password'/Please fill out at least {0} of these fields."));