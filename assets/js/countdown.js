jQuery(document).ready(function() {

    function calculateHMSleft() {
        //calculate
        var now = new Date();
        var hoursleft = 23 - now.getHours();
        var minutesleft = 59 - now.getMinutes();
        var secondsleft = 59 - now.getSeconds();
    
        //format 0 prefixes
        if (hoursleft < 10) hoursleft = "0" + hoursleft;
        if (minutesleft < 10) minutesleft = "0" + minutesleft;
        if (secondsleft < 10) secondsleft = "0" + secondsleft;
    
        //display
        var timer_wrapper = jQuery('.ux-timer-wrapper');
        timer_wrapper.each(function(){
            jQuery(this).find(".countdown-day").html('00');
            jQuery(this).find(".countdown-hour").html(hoursleft);
            jQuery(this).find(".countdown-minute").html(minutesleft);
            jQuery(this).find(".countdown-second").html(secondsleft);
        });
      }
    
      calculateHMSleft();
      setInterval(calculateHMSleft, 1000);
});

