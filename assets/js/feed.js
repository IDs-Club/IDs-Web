/*
* FeedEk jQuery RSS/ATOM Feed Plugin v1.1.2
* http://jquery-plugins.net/FeedEk/FeedEk.html
* Author : Engin KIZIL 
* http://www.enginkizil.com 
* 
* Hacked by https://github.com/cnsaturn
*/
(function (e) {
    e.fn.FeedEk = function (t) {
        var n = {
            FeedUrl: "http://idsclub.diandian.com/rss",
            MaxCount: 15,
            ShowDesc: true,
            ShowPubDate: true,
            CharacterLimit: 0,
            TitleLinkTarget: "_blank"
        };
        if (t) {
            e.extend(n, t)
        }
        var r = e(this).attr("id");
        var i;
        e.ajax({
            url: "http://ajax.googleapis.com/ajax/services/feed/load?v=1.0&num=" + n.MaxCount + "&output=json&ts=" +  new Date().getTime() + "&q=" + encodeURIComponent(n.FeedUrl) + "&hl=en&callback=?",
            dataType: "json",
            success: function (t) {
                e("#" + r).empty();
                var s = "";
                e.each(t.responseData.feed.entries, function (e, t) {
                    s += '<section class="posts"><article class="text"><header>';
                    if (n.ShowPubDate) {
                        i = new Date(t.publishedDate);
                        s += '<a href="' + t.link + '" target="' + n.TitleLinkTarget + '" >' + i.toLocaleDateString() + "</a>";
                    }
                    s += '<h2><a href="' + t.link + '" target="' + n.TitleLinkTarget + '" >' + t.title + "</a></h2>";
                    s += '</header>';
                    if (n.ShowDesc) {
                        if (n.DescCharacterLimit > 0 && t.content.length > n.DescCharacterLimit) {
                            s += '<section>' + t.content.substr(0, n.DescCharacterLimit) + "...</section>"
                        } else {
                            s += '<section>' + t.content + "</section>"
                        }
                    }
                    s += '</section>';
                });
                e("#" + r).append(s)
            }
        })
    }
})(jQuery);