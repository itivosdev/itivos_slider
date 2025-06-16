$(function () {
  $("#sortable_slider").sortable({
        placeholder: "ui-state-highlight",
        forcePlaceholderSize: true,
      });
  $("#sortable_slider").sortable({
      start: function(event,ui){
        $(".element_iv").toggleClass('element_iv pasive_iv');
        $("*").removeClass("clase_a_editar");
        $("*").removeClass("active_element_iv");            
        $("*").removeClass("over");
      },
      stop: function(event, ui){
        //$(".pasive_iv").toggleClass('pasive_iv element_iv');
        updateSliderOrder();
      }
  });
});
function updateSliderOrder()
{
  var order = $('#sortable_slider div').children('img').map(function(){
    return $(this).attr('slider_link');
  }).get();
  url_site = $("#url_site").val();
  admin_uri = $("#admin_uri").val();
  url = url_site+admin_uri+"/module/itivos_slider/sliders/ajax";
  $.ajax({
    url: url,
    type: "POST",
    data: {order:order, action: "ajax", resource:"update_order" },
    success: function (results) {
        tooltip(results,true);
    }
  });
}