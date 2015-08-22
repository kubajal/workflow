// THIS FILE RUNS AFTER THE PAGE LOADS
$(function() {

    /* SEARCH BOX BEHAVIOR
    ==========================================================================*/
    $('.right-nav .search input').each(function() {
        var defaultVal = $(this).val();
        $(this).focus(function() {
            $(this).removeClass('disabled');
            if($(this).val() == defaultVal)
                $(this).val('');
        }).blur(function() {
            if($(this).val() == '') {
                $(this).addClass('disabled');
                $(this).val(defaultVal);
            }
        });

        $(this).parent().submit(function() {
            return !$(this).children('input').hasClass('disabled');
        });
    });

    /* HOME PAGE SLIDER
    ==========================================================================*/
    $('.home-container .slider').each(function() {
        var delay = 7000;

        var slider = $(this).find('ul'),
            slides = slider.find('li'),
            slideCount = slides.length,
            currentSlide = 0,
            slideTimer;

        slides.not(':first').hide();

        var slideToIndex = function(i) {
            i = i%slideCount; // loop from end to start

            if (currentSlide == i)
                return;

            var hideSlide = slides.eq(currentSlide).stop(),
                showSlide = slides.eq(i).stop();

            if (i > 0 && (currentSlide == 0 || i < currentSlide)) {
                showSlide.show();
                hideSlide.show().fadeOut('slow');
            }
            else {
                hideSlide.show();
                showSlide.hide().fadeIn('slow', function() {
                    hideSlide.hide();
                });
            }

            $('div.slider-controller').children().removeClass('active').eq(i).addClass('active');

            var slideDelay = (i==0 ? delay*2 : delay);
            slideTimer = setTimeout(advanceSlide, slideDelay);

            currentSlide = i;
        }

        var advanceSlide = function() {
            slideToIndex(currentSlide + 1);
        }

        slideTimer = setTimeout(advanceSlide, delay);

        // stop autoplay when mouse over slide
        slider.bind('mouseenter', function() {
            clearTimeout(slideTimer);
        }).bind('mouseleave', function() {
            clearTimeout(slideTimer);
            slideTimer = setTimeout(advanceSlide, delay);
        });

        var controller = $('<div class="slider-controller"></div>').appendTo($(this));

        for (var i=0; i < slideCount; i++) {
            $('<a href="#"' + (i==0 ? ' class="active"' : '') + '></a>')
                .bind('click',(function (i) {
                    return function() {
                        clearTimeout(slideTimer);
                        slideToIndex(i);

                        return false;
                    };
                })(i))
                .appendTo(controller);
        }

        var controlWidth = controller.children().outerWidth(true);

        controller.css({width: (slideCount*controlWidth) + 'px'});
    });


    /* DOCUMENT LIST AND GRID VIEW
    ==========================================================================*/

    $('.docthumb img.thumbnail').mousemove(function(e) {
        var y = (e.pageY - $(this).offset().top)/210;
        var numImages = $(this).parent().siblings('.thumbnaillink').size() + 1;
        var idx = Math.max(Math.min(numImages-1, Math.floor(y * numImages)),0);
        $(this).parents('.docthumb').find('img.thumbnail[page][page='+idx+']')
            .css("z-index", 1).show();
        $(this).parents('.docthumb').find('img.thumbnail[page][page!='+idx+']')
            .css("z-index", 2).hide();
    });

    $('.docthumb img.thumbnail[page!=0]').hide();


    /* TEMPLATES
    ==========================================================================*/

    $('.template-delete').click(function(e) {
        e.preventDefault();

        if ($('#template-delete-div').length == 0) {
            $('<div id="template-delete-div" title="Confirm Delete">'
                + '<p>Are you sure you want to delete this template?</p></div>')
                .hide()
                .appendTo('body')
                .dialog({
                    resizable: false,
                    modal: true,
                    autoOpen: false,
                    minHeight: 10
                });
        }

        var url = $(this).attr('href');

        $('#template-delete-div').dialog('option', 'buttons', {
            'Continue': function() {
                window.location.href = url;
            },
            'Cancel': function() {
                $(this).dialog('close');
            }
        });

        $('#template-delete-div').dialog('open');
    });


    /* COMMUNITY EXAMPLES
    ==========================================================================*/
    $(".stars .star").mouseover(function(ev) {
        var self = this;
        var done = false;
        $(".stars .star").each(function(ev2) {
            if (!done) $(this).addClass("hover");
            done = done || this === self;
        });
    });

    $(".stars .star").mouseout(function(ev) {
        $(".stars .star").removeClass("hover");
    });

    $('.community-menu').mouseenter(function (ev) {
        var menu = $(this).find('ul.sub');
        menu.css({'max-height':'500px','border':'1px solid #ddd','overflow':'hidden'});
        menu.one('transitionend webkitTransitionEnd oTransitionEnd otransitionend MSTransitionEnd',function() {
            if(menu.height() > 5){
                menu.css('overflow','visible');
            }
        });
    }).mouseleave(function (ev) {
        var menu = $(this).find('ul.sub');
        menu.css({'max-height':'0px','overflow':'hidden'});
        menu.one('transitionend webkitTransitionEnd oTransitionEnd otransitionend MSTransitionEnd',function() {
            if(menu.height() < 5){
                menu.css('border','0');
            }
        });
        setTimeout(function() {
            if(menu.height() < 5){
                menu.css('border','0');
            }
        }, 100);
    }).find('li.sub-base').mouseover(function (ev) {
        $(this).find('ul.sub-sub').show();
    }).mouseout(function (ev) {
        $(this).find('ul.sub-sub').hide();
    });
    $('.sub-sub').hide();

    /* IMAGE LIGHTBOX
    ==========================================================================*/
    if ($.fancybox) {
        $('#content a.lightbox').fancybox({titleShow: false});

        $('#content a.lightbox-yt').click(function() {
            $.fancybox({
                        'padding'       : 0,
                        'autoScale'     : false,
                        'transitionIn'  : 'none',
                        'transitionOut' : 'none',
                        'title'         : this.title,
                        'width'         : 720,
                        'height'        : 405,
                        'href'          : this.href.replace(new RegExp("watch\\?v=", "i"), 'v/'),
                        'type'          : 'swf',
                        'swf'           : {
                            'wmode'             : 'transparent',
                            'allowfullscreen'   : 'true'
                        }
                    });

            return false;
        });

        $('#content a.lightbox-vimeo').click(function() {
            $.fancybox({
                        'padding'       : 0,
                        'autoScale'     : false,
                        'transitionIn'  : 'none',
                        'transitionOut' : 'none',
                        'title'         : this.title,
                        'width'         : 720,
                        'height'        : 405,
                        'href'          : this.href.replace(new RegExp("([0-9])","i"),'video/$1').replace(new RegExp("(vimeo.com)","i"),'player.$1'),
                        'type'          : 'iframe'
                    });

            return false;
        });
    }


    /* FOOTER SUB-MENU TOGGLING
    ==========================================================================*/
    $('#footer a.submenu').click(function(event) {
        event.preventDefault();
        var delay = 0;
        $(this).parent().siblings().find('a.submenu.active').removeClass('active').each(function() {
            $($(this).attr('href')).animate({'width': 'toggle'}, {duration: 200});
            delay = 200;
        });
        if ($(this).hasClass('active')) {
            $($(this).attr('href')).delay(delay).animate({'width': 'toggle'}, {duration: 200});
            $(this).removeClass('active');
        }
        else {
            $(this).addClass('active');
            $($(this).attr('href')).delay(delay).animate({'width': 'toggle'}, {duration: 200});

        }
    });


    /* MAKE LINKS FROM URLS IN FORUM POSTS
    ==========================================================================*/
    $('#content div.postbody').each(function() {
        var html = $(this).html();

        // replace anchor tags (make sure target=_blank)
        var exp = /<a[^>]*?href=['"]\s*?([^'"]*?)\s*?['"][^>]*?>/ig;
        html = html.replace(exp,' <a href="$1" target="_blank">');

        // replace http or ftp urls
        exp = /\s((https?|ftp):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/ig;
        html = html.replace(exp,' <a href="$1" target="_blank">$1</a>');

        // replace www. urls
        exp = /\s(www.[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/ig;
        html = html.replace(exp,' <a href="http://$1" target="_blank">$1</a>');

        $(this).html(html);
    });

    /* CONTACT SALES LINKS
    ==========================================================================*/
    if (typeof lucid !== 'undefined' && lucid.view && lucid.view.Dialog && typeof hbspt !== 'undefined') {
        var product = window.lucidConfigure && window.lucidConfigure['appname'] || 'chart';
        var portalId = window.lucidConfigure && window.lucidConfigure['portal-id'] || '442690';

        var buildHubspotDialog = function (data) {

            var dialog = new lucid.view.Dialog({}, {
                external: true, // indicate that we're calling Dialog from uncompiled code
                extraClasses: 'flat-dialog',
                closeButton: true,
                modal: true,
                width: 540,
                id: data.dialogId
            });

            data.linkDom.click(function(e) {
                dialog.open();
                _kmq.push(['record', data.kissMetricsMessage, {'via': this.id}]);

                e.preventDefault();
                return false;
            });

            hbspt.forms.create({
                portalId: portalId,
                formId: data.formId,
                redirectUrl: data.redirectUrl || window.location.href, // appears to only work if the "embed" configuration in the hubspot console has an empty redirect URL
                formData: {
                    cssClass: 'hs-form stacked lucid-hubspot-form'
                },
                // Our other forms (i.e. in chart-web) just use lucid-hubspot-submit, but the inheritance heirarchy that defines that class involves pulling
                // in CSS meant only for the pricing page. There's no reason to make all of our code dependent on that. The CSS could be refactored to be
                // more modular.
                submitButtonClass: 'lucid-hubspot-submit btn btn-caps btn-blue',
                target: '#' + data.dialogId + '-dialog-body'
            });
        };

        var contactSalesLink = $('a.contact_sales_link');
        var contactUsLink = $('a.contact_us_link');
        var whitepaperLinks = $('a.whitepaper_link');

        if (contactSalesLink.length > 0) {

            var contactSalesData = {
                formId: window.lucidConfigure && window.lucidConfigure['contactSalesForm_' + product] || "f3065afc-5faf-475b-bad8-a9eff30c8b6e",
                dialogId: 'contact-sales-hs',
                linkDom: contactSalesLink,
                kissMetricsMessage: 'Invoked contact sales dialog'
            };

            buildHubspotDialog(contactSalesData);
        }

        if (contactUsLink.length > 0) {

            var contactUsData = {
                formId: window.lucidConfigure && window.lucidConfigure.contactUsForm || "3e6b600d-441d-43b2-87ca-42cbf0f65581",
                dialogId: 'contact-us-hs',
                linkDom: contactUsLink,
                kissMetricsMessage: 'Invoked contact us dialog'
            };

            buildHubspotDialog(contactUsData);
        }

        if (whitepaperLinks.length > 0) {

            var whitepaperData = {
                formId: window.lucidConfigure && window.lucidConfigure['enterpriseSalesForm_' + product] || "15e98390-fb30-41de-b879-1a40a7d57f69",
                dialogId: 'whitepaper-hs',
                linkDom: whitepaperLinks,
                kissMetricsMessage: 'Invoked whitepaper enterprise dialog',
                redirectUrl: whitepaperLinks[0].href
            };

            buildHubspotDialog(whitepaperData);
        }
    }

    /* GOOGLE SSO LINKS
    ==========================================================================*/
    // we are using this function to prevent double-clicking which may occasionally cause a db error due to duplicate primary keys
    $('a[href^="/users/googleFederatedLogin"]').click(function() {
        var me,
            url,
            elem = $(this);
        if (elem.attr('href') && elem.attr('href') != "null") {
            url = elem.attr('href');
            setTimeout(function() {
                elem.prop('href', null);
                elem.removeAttr('href');
            }, 1);

            var target = elem.prop('target');
            if (target != '_blank' || target != '_top' || target != '_parent')
                window.location=url;
        }
        setTimeout(function(){
            elem.prop('href', url);
        }, 2000)
    });

    /* NAVBAR DROPDOWN
    ===========================================================================*/
    var navTabMenu;
    $('#navbar-tab').click(function(e) {
        e.preventDefault();
        e.stopPropagation();

        if (!navTabMenu) {
            var menuContainer = $('.menu-container'),
                appId = (window.lucidConfigure && window.lucidConfigure.appid) || parseInt($(this).data('app-id') || 0, 10) || 0;

            if (!menuContainer.length) {
                menuContainer = $('<div class="menu-container"></div>').appendTo('body');
            }

            navTabMenu = $('<div class="menu" style="right: 40px; top: ' + ($(this).offset().top + $(this).outerHeight() + 1) + 'px;">' +
                '<div class="menu-body noScrollBars">' +
                    (appId == 0 ?
                    '<a class="menu-item menu-item-tight" href="/switchProduct/produce/press">Go to Lucidpress</a>' :
                    '<a class="menu-item menu-item-tight" href="/switchProduct/produce/chart">Go to Lucidchart</a>') +
                    '<div class="menu-separator"></div>' +
                    '<a id="navbar-tabmenu-settings" class="menu-item menu-item-tight" href="/users/settings">User settings</a>' +
                    '<a id="navbar-tabmenu-logout" class="menu-item menu-item-tight" href="/users/logout?otherDomains=chart,press">Log out</a>' +
                '</div></div>').appendTo(menuContainer);

            $(document).click(function() {
                navTabMenu.hide();
            });
        }
        else
            navTabMenu.toggle();
    });

    /* NAVBAR DROPDOWN FOR NEW PRESS
    ===========================================================================*/
    var pressDropdown;
    $('#dropdownMenu').click(function(e) {
        e.preventDefault();
        e.stopPropagation();

        if (!pressDropdown) {
            var menuContainer = $('.menu-container'),
                appId = (window.lucidConfigure && window.lucidConfigure.appid) || parseInt($(this).data('app-id') || 0, 10) || 0;

            if (!menuContainer.length) {
                menuContainer = $('<div class="menu-container"></div>').appendTo('#dropdown');
            }

            pressDropdown = $('<div class="options-menu">' +
                '<ul class="menu-body">' +
                    (appId == 0 ?
                    '<li><a href="/switchProduct/produce/press">Go to Lucidpress</a></li>' :
                    '<li><a href="/switchProduct/produce/chart">Go to Lucidchart</a></li>') +
                    '<li><a id="" class="" href="/users/settings">User settings</a></li>' +
                    '<li><a id="" class="" href="/users/logout">Log out</a></li>' +
                '</ul></div>').appendTo(menuContainer);

            $(document).click(function() {
                pressDropdown.hide();
            });
        }
        else
            pressDropdown.toggle();
    });
          //Responsive menu
    $('#rmenu').click(function(){
     var txt = $("#navigation").is(':visible') ? 'Menu' : 'X';
     $("#rmenu").text(txt);
$('#navigation').slideToggle("fast");
    });
    //Responsive menu for examples
    $('#responsive-btn').click(function(){
     var txt = $("#responsive-nav").is(':visible') ? 'More Examples' : 'Hide Examples';
     $("#responsive-btn").text(txt);
$('#responsive-nav').slideToggle("fast");
    });

    //Shortens username in login button when displayed
    var usrName = $('.usr').text().length;
    if (usrName >= 14) {
        $('.usr').addClass('shorten');
    }
    /* AUTOMATIC SLIDER
    ============================================================================*/
    var currentPosition = 0;
        var slideWidth = 100;
        var slides = $('#auto .slide');
        var numberOfSlides = slides.length;
        var slideShowInterval;
        var speed = 7500;

        slideShowInterval = setInterval(changePosition, speed);

        manageNav(currentPosition);

        $('#auto .slideNav').click(function() {

            currentPosition = ($(this).attr('id')=='rightNav')
            ? currentPosition+1 : currentPosition-1;

            manageNav(currentPosition);
            clearInterval(slideShowInterval);
            slideShowInterval = setInterval(changePosition, speed);
            moveSlide();
        });

        function manageNav(position) {
            if(position==0){ $('#auto #leftNav').hide() }
            else { $('#auto #leftNav').show() }
            if(position==numberOfSlides-1){ $('#auto #rightNav').hide() }
            else { $('#auto #rightNav').show() }
        }
        function changePosition() {
            if(currentPosition == numberOfSlides - 1) {
                currentPosition = 0;
                manageNav(currentPosition);
            } else {
                currentPosition++;
                manageNav(currentPosition);
            }
            moveSlide();
        }
            slides.bind('mouseenter', function() {
            clearInterval(slideShowInterval);
            var slides = $('#auto .slide');
        }).bind('mouseleave', function() {
            manageNav(currentPosition);
            clearInterval(slideShowInterval);
            slideShowInterval = setInterval(changePosition, speed);
            moveSlide();
        });

        function moveSlide() {
                $('#auto .slider-wrapper')
                  .animate({'marginLeft' : slideWidth*(-currentPosition) + '%'});
        }
        $("#auto .carousel-next, #auto .carousel-prev").click(function(e) {
    e.preventDefault();
});
    /* PREFERENCES PAGE SIZE
    =============================================================================*/
    if(window['defaultPageSize']){
        var customOption = $('#DefaultPageSize').find('option').last(),
            pageSizeWidth = $('#PageSizeWidth'),
            pageSizeHeight = $('#PageSizeHeight'),
            units = $('input[name=DefaultPageUnits]:checked').val(),
            regex = /\d+\.?\d{0,2}/;

        if(defaultPageSize.lastIndexOf('Custom',0) == 0){
            $('#DefaultCustomInputs').show();
            customOption.val(defaultPageSize);
            customOption.attr('selected','true');
            //looks like "Custom 7x4 in"
            var size = defaultPageSize.split(' ')[1].split('x');
            var defaultPageSizeUnits = defaultPageSize.split(' ')[2];
            pageSizeWidth.val(size[0] + " " + defaultPageSizeUnits);
            pageSizeHeight.val(size[1] + " " + defaultPageSizeUnits);
        }

        $('#DefaultPageSize').change(function(e){
            var value = $(this).val();
            if(value.lastIndexOf('Custom', 0) == 0){
                $('#DefaultCustomInputs').show();
                updateCustom();
            } else {
                $('#DefaultCustomInputs').hide();
            }
        });

        $('input[name=DefaultPageUnits]').change(function(e){
            if(units != $(this).val()){
                units = $(this).val();
                if(pageSizeWidth.val().match(regex) && pageSizeHeight.val().match(regex)){
                    if(units == 'cm'){
                        pageSizeWidth.val(Math.round(100*parseFloat(pageSizeWidth.val().match(regex)[0])*2.54)/100 + ' ' + units);
                        pageSizeHeight.val(Math.round(100*parseFloat(pageSizeHeight.val().match(regex)[0])*2.54)/100 + ' ' + units);
                    } else {
                        pageSizeWidth.val(Math.round(100*parseFloat(pageSizeWidth.val().match(regex)[0])/2.54)/100 + ' ' + units);
                        pageSizeHeight.val(Math.round(100*parseFloat(pageSizeHeight.val().match(regex)[0])/2.54)/100 + ' ' + units);
                    }
                    updateCustom();
                }
            }
        })

        function updateCustom(){
            var width = pageSizeWidth.val().match(regex);
            var height = pageSizeHeight.val().match(regex);
            if(!width){
                width = units == 'in' ? 8.5 : 21;
                pageSizeWidth.val(width + ' ' + units);
            }
            else{
                width = width[0];
            }
            if(!height){
                height = units == 'in' ? 11 : 29.7;
                pageSizeHeight.val(height + ' ' + units);
            }
            else {
                height=height[0];
            }
            defaultPageSize = "Custom " + width + "x" + height + " " + units;
            customOption.val(defaultPageSize);
        }

        function bindPageDimension(elem,width){
            elem.change(function(){
                var value = $(this).val();
                var num = value.match(regex);
                if(num && num.length > 0){
                    $(this).val(num[0] + ' ' + units);
                } else {
                    $(this).val(defaultPageSize.split(' ')[1].split('x')[width?0:1] + ' ' + units);
                }
                updateCustom();
            }).click(function(){
                $(this).select();
            });
        }

        bindPageDimension(pageSizeWidth,true);
        bindPageDimension(pageSizeHeight,false);
    }

    /* THE USER REGISTERED - WHAT JAVASCRIPT DO YOU WANT TO EXCECUTE? (This will be executed the next time the user hits an accounts page)
    =============================================================================*/
    if($.cookie('AnalyticsSignupInfo')) {
        var register = JSON.parse($.cookie('AnalyticsSignupInfo'));

        // QUANTCAST
        window['_qevents'] && _qevents.push({qacct:"p-09QkjQEWFRg8A", labels:"_fp.event.Registration+" + register['accountType']});

        $.cookie('AnalyticsSignupInfo', null);
    }

    /* THE USER PAID - WHAT JAVASCRIPT DO YOU WANT TO EXCECUTE? (This will be executed the next time the user hits an accounts page)
    =============================================================================*/
    if($.cookie('AnalyticsPaidInfo')) {
        var payment = JSON.parse($.cookie('AnalyticsPaidInfo'));

        // QUANTCAST
        window['_qevents'] && _qevents.push({qacct:"p-09QkjQEWFRg8A", labels:"_fp.event.Purchase " + payment['level_slug'], revenue:payment['cost_in_cents']});

        $.cookie('AnalyticsPaidInfo', null);
    }

    /* THE USER CANCELED THEIR SUBSCRIPTION - WHAT JAVASCRIPT DO YOU WANT TO EXCECUTE? (This will be executed the next time the user hits an accounts page)
    =============================================================================*/
    if($.cookie('AnalyticsCanceledSubscription')) {

        // QUANTCAST
        window['_qevents'] && _qevents.push({qacct:"p-09QkjQEWFRg8A", labels:"_fp.event.CanceledSubscription"});

        $.cookie('AnalyticsCanceledSubscription', null);
    }

    /* AUTOPLAY VIDEO WHEN "autoplayVideo" IS FOUND IN window.location.hash
    =============================================================================*/

    if (/autoplayVideo/.test(window.location.hash)) {
        $('.autoplay-with-hash').first().click();
    }

    /* AUTORESIZE SLIDER TITLE WHEN TOO LARGE
    =============================================================================*/

    $('.lucid .slider .bg-white h1').not('.lucid .slider .bg-white div.grid_12.alpha h1').each(function() {
        if ($(this).text().length >= 21) {
            $(this).addClass('smaller-title');
            $(".lucid .slider .bg-white p").css('font-size','18px');
        }
    });

});

function isValidEmail(email) {
    // First, we check that there's one @ symbol,
    // and that the lengths are right.
    if (!email.match(/^[^@]{1,64}@[^@]{1,255}$/)) {
      // Email invalid because wrong number of characters
      // in one section or wrong number of @ symbols.
      return false;
    }
    // Split it into sections to make life easier
    var email_array = email.split('@');
    var local_array = email_array[0].split('.');
    for (var i = 0; i < local_array.length; i++) {
        if (!local_array[i].match(/^(([A-Za-z0-9!#$%&'*+\/=?^_`{|}~-][A-Za-z0-9!#$%&'*+\/=?^_`{|}~\.-]{0,63})|(\"[^(\\|\")]{0,62}\"))$/)) {
            return false;
        }
    }
    // Check if domain is IP. If not,
    // it should be valid domain name
    if (!email_array[1].match(/^\[?[0-9\.]+\]?$/)) {
        var domain_array = email_array[1].split('.');
        if (domain_array.length < 2) {
            return false; // Not enough parts to domain
        }
        for (var j = 0; j < domain_array.length; j++) {
            if(!domain_array[j].match(/^(([A-Za-z0-9][A-Za-z0-9-]{0,61}[A-Za-z0-9])|([A-Za-z0-9]+))$/)) {
                return false;
            }
        }
    }
    return true;
}

// extract valid email addresses from a block of text
function getEmailsFromText(emails) {
    // get rid of quoted local parts
    emails = emails.replace(/\"[^\"]+\"/g, '');

    // get rid of angle brackets
    emails = emails.replace(/<([^>]+)>/g, ' $1 ');

    // replace all delimiters with a space
    emails = emails.replace(/[\s,]+/g, ' ');
    emails = emails.split(' ');

    var ret = [];

    for (var i = 0; i < emails.length; i++)
        if (isValidEmail(emails[i]))
            ret.push(emails[i]);

    return ret;
}

// extract valid email addresses from a block of text
function getInvalidEmailsFromText(emails) {
    // get rid of quoted local parts
    emails = emails.replace(/\"[^\"]+\"/g, '');

    // get rid of angle brackets
    emails = emails.replace(/<([^>]+)>/g, ' $1 ');

    // replace all delimiters with a space
    emails = emails.replace(/[\s,]+/g, ' ');

    // trim leading and trailing spaces
    emails = emails.replace(/^\s*(\S+[\s\S]*\S+)\s*$/, '$1');

    emails = emails.split(' ');

    var ret = [];

    for (var i = 0; i < emails.length; i++)
        if (!isValidEmail(emails[i]))
            ret.push(emails[i]);

    return ret;
}

function validateAndPostEnterpriseRequest(form, success, error, idPrefix) {
    idPrefix = '#' + (idPrefix || '') + 'enterprise_request_';

    if(!$(idPrefix + 'first_name').val())
        return alert("First Name is required");
    if(!$(idPrefix + 'last_name').val())
        return alert("Last Name is required");
    if(!$(idPrefix + 'email').val())
        return alert("Email is required");
    if(!(isValidEmail($(idPrefix + 'email').val())))
        return alert("Email Address is invalid");
    if(!$(idPrefix + 'phone').val())
        return alert("Phone is required");
    if(!$(idPrefix + 'title').val())
        return alert("Title is required");
    if(!$(idPrefix + 'company').val())
        return alert("Company Name is required");
    var company_size = $(idPrefix + 'company_size').val();
    if(company_size.length > 0 && isNaN(company_size * 1))
        return alert('Company Size must be a number');

    $.ajax({
        'type':'POST',
        'url':'/bugs/report?NotError=0',
        'data':form.find('textarea, input').serialize(),
        'success':success,
        'error':error,
        'complete':function() {}
    });
};

function flash(message, type) {
    if ($('#messagebar').length == 0)
        $('<div id="messagebar" class="grid_24"></div>').insertAfter('#primary-nav');

    $('#messagebar').empty().append(message);
    type = type || 'success';
    $('body').removeClass('success').removeClass('message').removeClass('alert').removeClass('error').addClass(type);
    $(window).scrollTop(0);
}

if (window.lucid !== undefined) {
    lucid.cookie = lucid.cookie || {};
    lucid.cookie = $.extend(lucid.cookie, {

        test: function() {
            // check our local cookie test
            var cookie = $.cookie('lucid_cookie_test_local');
            if ( !cookie ) {
                flash(lucid.i18n('lucid.cookie.test.fail.local'), 'error');
            } else if ( cookie == 'dev' ) {
                if ( console ) console.log('Bypassing third party cookie test.');
            } else {
                var script = document.createElement('script');
                script.setAttribute('src', 'https://cookietest.lucidchart.com/util/cookieTest');
                $('head').append(script);
            }
        },

        check: function() {
            var script = document.createElement('script');
            script.setAttribute('src', 'https://cookietest.lucidchart.com/util/cookieTest/verification');
            $('head').append(script);
        },

        results: function(success) {
            if ( !success ) {
                flash(lucid.i18n('lucid.cookie.test.fail.remote'), 'error');
            }
        }

    });
}

/**
 * =======================================================================
 * Drop zone component
 * =======================================================================
 */
var lucid = lucid || {};

/**
 * @constructor
 * Create a VisioDropZone object
 * @param {HTMLElement} elem the element to turn into a Visio drag and drop area
 */
lucid.VisioDropZone = function(elem) {
    // create a container for the dropzone
    var component = $('<div>').get(0);
    $(elem).append(component);
    this.component = this.toDropZone(component);

    this.fileName = '';

    this.createDom();
    this.createCallbacks();
    this.showNoUpload();
}

/**
 * Create the DOM to go within the div provided for the drop zone
 */
lucid.VisioDropZone.prototype.createDom = function() {
    // initialize the mask
    var mask = $('<div>').addClass('vs-upload-mask').get(0);
    $(this.component).append(mask);
    this.mask = this.toDropZone(mask);

    // create the header area
    var header = $('<h1>').addClass('vs-header vs-upper').get(0);
    $(this.component).append(header);
    this.header = $(header);

    // create the image
    var image = $('<img>').addClass('vs-img').get(0);
    $(this.component).append(image);
    this.image = $(image);

    // create the footer area
    var footer = $('<div>').addClass('vs-footer').get(0);
    $(this.component).append(footer);
    this.footer = $(footer);

    // create the footer area for the no upload view
    this.noUploadFooter = $(
        '<div>' +
            '<div class="vs-upload-btn-container">' +
                '<div class="vs-or-container">' +
                    '<span class="vs-or">or</span>' +
                '</div>' +
                '<input type="button" class="btn btn-secondary vs-upper" value="choose file" />' +
            '</div>' +
            '<p class="vs-supports">(support for .vdx, .vsd, and .vsdx version 2003 and later)</p>' +
        '</div>'
    ).get(0);

    // create the footer area for the dragging view
    this.dragOverFooter = $(
        '<div>' +
            '<h1>Incoming!</h1>' +
            '<p class="vs-instructions">Drop your file to instantly view it in Lucidchart for Free!</p>' +
        '</div>'
    ).get(0);

    // create a hidden file input for users who want to choose their file manually
    var wrapper = $('<div>').css({
        'height': 0,
        'width': 0,
        'overflow': 'hidden'
    });
    var fileInput = $('<input type="file">').get(0);
    $(this.component).append(fileInput);
    this.fileInput = $(fileInput);
    this.fileInput.wrap(wrapper);
    this.fileInput.change(function() {
        var file = this.fileInput.get(0).files[0];
        this.fileName = escape(file.name);
        this.uploadFile(file);
    }.bind(this));

    // create an error dialog
    var errorDialog = $(
        '<div class="vs-error">' +
            '<div class="vs-error-container">' +
                '<h2>Upload Error</h2>' +
                '<p>Failed to upload Visio File.</p>' +
                '<p>Please check the file format or internet connection. ' +
                    '<a href="http://support.lucidchart.com/entries/22201888-VSD-VDX-or-VSDX-import-failed" class="external-link" rel="nofollow">Learn more</a>' +
                '</p>' +
                '<div class="vs-error-footer">' +
                    '<input type="button" class="btn btn-secondary" value="Ok" />' +
                '</div>' +
            '</div>' +
        '</div>'
    ).get(0);
    $(this.component).append(errorDialog);
    this.errorDialog = $(errorDialog)
    this.errorDialog.find('.btn').click(function() {
        this.errorDialog.hide();
    }.bind(this));
    this.errorDialog.hide();
}

/**
 * Switch the view state to the no upload view
 */
lucid.VisioDropZone.prototype.showNoUpload = function() {
    $(this.component).attr('class', 'vs-no-upload');
    this.header.html('<strong>drop visio file</strong> to upload');
    this.image.attr('src', 'https://d2slcw3kip6qmk.cloudfront.net/app/webroot/css/sites/chart/visio-dropzone/images/vs-no-upload.png');
    this.footer.empty().append(this.noUploadFooter);

    // make the upload button affect the hidden file input
    $(this.noUploadFooter).find('.btn').click(function() {
        this.fileInput.click();
    }.bind(this));
}

/**
 * Switch the view state to the dragging view
 */
lucid.VisioDropZone.prototype.showDragOver = function() {
    $(this.component).attr('class', 'vs-drag-over');
    this.header.empty();
    this.image.attr('src', 'https://d2slcw3kip6qmk.cloudfront.net/app/webroot/css/sites/chart/visio-dropzone/images/vs-drag-over.png');
    this.footer.empty().append(this.dragOverFooter);
}

/**
 * Switch the view state to the uploading view
 */
lucid.VisioDropZone.prototype.showLoading = function() {
    $(this.component).attr('class', 'vs-loading');
    this.header.empty();
    this.image.attr('src', 'https://d2slcw3kip6qmk.cloudfront.net/app/webroot/css/sites/chart/visio-dropzone/images/vs-drag-over.png');
    this.footer.empty().append(
        '<div>' +
            '<p class="vs-file-name">' + this.fileName + '</p>' +
            '<div class="vs-progress">' +
                '<div class="progress-bar-indeterminate"></div>' +
            '</div>' +
        '</div>'
    );
}

/**
 * Upload a visio file to the visio service
 * @param {File} file the file to upload to the visio service
 */
lucid.VisioDropZone.prototype.uploadFile = function(file) {
    var me = this;

    // clear the file input for future use
    this.fileInput.wrap('<form>').closest('form').get(0).reset();
    this.fileInput.unwrap();

    // switch to the uploading view
    this.showLoading();

    var form = new FormData();
    form.append('file', file);
    form.append('name', file.name);
    $.ajax({
        'url': '/visio/openConversions',
        'type': 'POST',
        'data': form,
        'error': function() {
            me.errorDialog.show();
            me.showNoUpload();
        },
        'success': function(data) {
            me.showDone();
            setTimeout(function() {
                window.location = data['viewer'];
            }, 1000);
        },
        'processData': false,
        'contentType': false,
        'cache': false
    });
}

/**
 * Switch the view state to the done uploading view
 */
lucid.VisioDropZone.prototype.showDone = function() {
    $(this.component).attr('class', 'vs-done');
    this.header.empty();
    this.image.attr('src', 'https://d2slcw3kip6qmk.cloudfront.net/app/webroot/css/sites/chart/visio-dropzone/images/vs-done.png');
    this.footer.empty().append(
        '<div>' +
            '<p class="vs-file-name">' + this.fileName + '</p>' +
            '<p class="vs-open">Opening File...</p>' +
        '</div>'
    )
}

/**
 * Create the callbacks for drag events
 */
lucid.VisioDropZone.prototype.createCallbacks = function() {
    var me = this;
    // set the dragenter callback on the component passed in, and show the invisible mask.
    // We then define the remaining callbacks on the mask, because events will fire on the original
    // component whenever we enter a child element. Defining the dragleave event on an invisible
    // mask that overlays the component keeps this from happening.
    this.component.dragCallback('dragenter', function(event) {
        // don't go into drag mode if the error dialog is shown
        if (me.errorDialog.is(':visible')) {
            return;
        }
        me.showDragOver()
        $(me.mask).show();
    });

    this.mask.dragCallback('dragleave', function(event) {
        me.showNoUpload();
        $(me.mask).hide();
    });

    this.mask.dragCallback('drop', function(event) {
        // don't upload if the error dialog is shown
        if (me.errorDialog.is(':visible')) {
            return;
        }

        var file = event.dataTransfer.files[0];
        me.fileName = escape(file.name);
        $(me.mask).hide();
        me.uploadFile(file);
    });

    // do nothing on the drag over event
    // we have to specify this because if this event gets propagated, the browser
    // will still carry out its default action
    this.mask.dragCallback('dragover', function(event) {});
};

/**
 * Create a function on the element that will make it easy to add drag event
 * callbacks that keep the browser from doing its default behavior (opening the file).
 * In the callback, 'this' refers to the element.
 */
lucid.VisioDropZone.prototype.toDropZone = function(elem) {
    elem.dragCallback = function(eventName, callback) {
        this.addEventListener(eventName, function(event) {
            event.preventDefault();
            event.stopPropagation();
            callback.call(this, event);
        });
    }.bind(elem);

    return elem;
};

$(document).ready(function() {
    $('.visio-dropzone').each(function() {
        new lucid.VisioDropZone(this);
    });
});

