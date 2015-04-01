jQuery(document).ready(function() {

    jQuery('#datepicker-example1').Zebra_DatePicker({
		format: 'M d, Y'
		});
	jQuery('#datepicker-example3').Zebra_DatePicker({
		format: 'M d, Y'
		});	

    jQuery('#datepicker-example2').Zebra_DatePicker({
        format: 'M d, Y'    // boolean true would've made the date picker future only
                        // but starting from today, rather than tomorrow
    });

  
});