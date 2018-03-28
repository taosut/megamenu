// JavaScript Document
$(document).ready(function() {
    var rows=$('table').find('tbody tr').length;
    var no_rec_per_page=2;
    var no_pages= Math.ceil(rows/no_rec_per_page);

    var $pagenumbers=$('<ul id="page"></ul>');
    $('<li class="pager pager1 active">01</li>').appendTo($pagenumbers);
    for(i=1;i<no_pages;i++)
    {
        $('<li class="pager pager'+(i+1)+'"><a href="#" onclick="JavaScript:return false;">'+padLeft((i+1),2)+'</a></li>').appendTo($pagenumbers);
    }
    $pagenumbers.appendTo($(".number"));

    $('.page').hover(
        function(){
            $(this).addClass('hover');
        },
        function(){
            $(this).removeClass('hover');
        }
    );
    $('table').find('tbody tr').hide();

    // var tr=$('table tbody tr');

    // for(var i=0;i<=no_rec_per_page-1;i++)
    // {
    //     $(tr[i]).show();
    // }

    $('li').click(function(event){
        // $('table').find('tbody tr').hide();
        // for(i=($(this).text()-1)*no_rec_per_page;i<=$(this).text()*no_rec_per_page-1;i++)
        // {
        //     $(tr[i]).show();
        // }
        //alert(parseInt($(this).text()));
        for(i=1;i<=$("#page").find('li').length;i++)
        {
            if( i < parseInt($(this).text())-3 || i > parseInt($(this).text())+3 )
            {
                $(".pager"+i).hide();
            }
            else
            {
                $(".pager"+i).show();
            }

            if( i == parseInt($(this).text()) )
            {
                $(".pager"+i).addClass("active").find("a").contents().unwrap();
            }
            else
            {
                $(".pager"+i).removeClass("active").html('<a href="#" onclick="JavaScript:return false;">'+padLeft((i),2)+'</a>');
            }
        }
    });
    $(".pager").click(function() {
      var num = $(this).html();
        $.post("library/set_info.php?type=ward_log_table",{Page:parseInt(num)},function(data){
          switch(data){
              case "E":
                  alert('錯誤。');
              break;
              default:
                  $('.buy_log_table').html() = data;
              break;
          }
        });
    });
    $.post
});

function padLeft(str, len) {
    str = '' + str;
    return str.length >= len ? str : new Array(len - str.length + 1).join("0") + str;
}