
$(document).on("focus",".entity-search",function() {
    var that = $(this);
    var thatHidden = $(this).prev();
    if (that.data('typeahead')) return;
    that.data('typeahead',true);

    var id = that.attr("id");

    var url = that.attr('data-search-url');
    var timeout = null;
    var lastQuerySent = null;

    that.typeahead({},{
        source: function(query,callback) {
            that.prev().val('');
            if (timeout) clearTimeout(timeout);

            // Don't send a query for a blank string
            if (query == '') {
                return [];
            }

            // Don't re-send a query if already displaying the results for that same query
            if (query == lastQuerySent) {
                return null;
            }

            // Wait briefly before sending the request
            timeout = setTimeout(function() {
                lastQuerySent = query;
                $.ajax({
                    url: url,
                    data: {
                        query: query
                    },
                    success: function (data) {
                        if (query != lastQuerySent) return; // Ignore AJAX responses to old queries

                        callback(data);
                    }
                });
            }, 150);
        },
        displayKey: 'title'
    }).on("typeahead:selected", function(obj, datum, name) {
        thatHidden.val(datum.id);
        that.val(datum.title);
        that.trigger("entityselected",datum);
    }).on("typeahead:autocompleted", function(obj, datum, name) {
        thatHidden.val(datum.id);
        that.val(datum.title);
        that.trigger("entityselected",datum);
    }).on("keypress", function(evt) {
        var keypress = false;
        if (typeof evt.which == "number" && evt.which > 0) {
            // In other browsers except old versions of WebKit, evt.which is
            // only greater than zero if the keypress is a printable key.
            // We need to filter out backspace and ctrl/alt/meta key combinations
            keypress = !evt.ctrlKey && !evt.metaKey && !evt.altKey && evt.which != 8;
        }
        if (keypress) {
            thatHidden.val("");
        }
    });
    $("#"+id).focus();
});
