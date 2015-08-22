var LucidAnalytics = {
    logUrl: '/analytics_events/create/',

    visitor_id: function() {
        var ret = $.cookie('lucidchart_analytics_visitor_id');
        if(ret == null)
            ret = 'NULL';

        return ret;
    },

    visit_id: function() {
        var ret = $.cookie('lucidchart_analytics_visit_id');
        if(ret == null)
            ret = 'NULL';

        return ret;
    },

    logRunning: false,

    log: function(document_id, from, to, done, customUrl) {
        if(this.logRunning) {
            setTimeout(function() {
                LucidAnalytics.log(document_id, from, to, done);
            }, 100);
            return;
        }

        this.logRunning = true;

        if(from == null)
            from = 'NULL';
        if(to == null)
            to = 'NULL';

        //Don't allow multiple actual actions from the same visitor
        //on the same day (but do allow multiple hits to the same
        //page).
        if(to != 'NULL') {
            var key = document_id+'-'+from+'-'+to;
            if($.cookie(key)) {
                this.logRunning = false;
                if(done)
                    done();
                return;
            }

            //A one-day blockout on this browser from sending the exact same
            //analytics information.
            $.cookie(key, 'set', { expires: 1, path:'/' });
        }

        var url = this.logUrl
                + encodeURIComponent(this.visitor_id()) + '/'
                + encodeURIComponent(this.visit_id()) + '/'
                + encodeURIComponent(document_id) + '/'
                + encodeURIComponent(from) + '/'
                + encodeURIComponent(to);

        var data = 'referer='+encodeURIComponent(document.referrer);
        data += '&url='+encodeURIComponent((customUrl ? customUrl : location.href));

        var retrying = false;

        $.ajax({
            type:'POST',
            url:url,
            data:data,
            dataType: "text",
            success:function(data) {
            },
            complete:function() {
                LucidAnalytics.logRunning = false;
                if(!retrying && done)
                    done();
            }
        });
    }
};

//Once per minute, while the page stays open, keep our visit alive.
//Give up after 2 hours.
var visit_renewal_minutes = 0;
var visit_renewal_interval = setInterval(function() {
    var visit_id = $.cookie('lucidchart_analytics_visit_id');
    if(visit_id) {
        var Expires = new Date();
        Expires.setTime(Expires.getTime() + (20 * 60 * 1000));
        $.cookie('lucidchart_analytics_visit_id', visit_id, {
            expires: Expires,
            path:'/'
        }, {secure: true});
    }

    visit_renewal_minutes++;
    if(visit_renewal_minutes >= 120)
        clearInterval(visit_renewal_interval);
}, 60000);
