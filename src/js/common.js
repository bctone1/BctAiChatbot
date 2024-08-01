jQuery(document).ready(function($) {
  /** gnb */
  $(function(){	
    $(".gnbDepth1 > li").on("mouseover focusin", showDepth2);
    $(".gnbDepth1 > li").on("mouseleave", hideDeptn2);
    $(".gnbDepth2 > li").on("mouseover focusin", showDepth3);
    $(".gnbDepth2 > li").on("mouseleave", hideDeptn3);
    $('.logo a').focusin(hideDeptn2);//제일 앞부분에서 focus 사라질 때
    $('.gnbDepth1 > li:last-child .gnbDepth2 > li:last-child').focusout(hideDeptn2);//제일 뒷부분에서 focus 사라질 때*/
  });
  
  //2depth menu : show
  function showDepth2(){
    $(".gnbDepth1 > li").removeClass("on");//reset
    $(this).addClass("on")
  };
  //2depth menu : hide
  function hideDeptn2(){
    $(".gnbDepth1 > li").removeClass("on")
  };

  //3depth menu : show
  function showDepth3(){
    $(".gnbDepth2 > li").removeClass("on");//reset
    $(this).addClass("on")
  };
  //3depth menu : hide
  function hideDeptn3(){
    $(".gnbDepth2 > li").removeClass("on")
  };


  /** slider-range */
  $(function() {
    $("#slider-range-min").slider({
      range: "min",
      value: 1234,
      min: 1,
      max: 3000,
      slide: function(event, ui) {
        $("#amount").val(ui.value);
      }
    });
    $("#amount").val($("#slider-range-min").slider("value"));
  });
});


/** modal popup */
// modal popup open
function openPop(modalname) {
  document.get
  $("." + modalname).addClass("on");

  $(document).ready(function () {
    // chatbotIconItem image size
    $(".popup.on .chatbotIconItem").find("img").each(function(){
      var imgWidth = $(this).width() / 2;
      var imgHeight = $(this).height() / 2;
      $(this).css({"width":imgWidth,"height":imgHeight});
    });
  })
}
     
jQuery(document).ready(function ($) {
  // modal popup close
  $(".popup .btnCloseMenu, .popup .menuArea botton, .popup .btnCloseMenuCircle, .btnCloseRed").on("click", function(){
    $(this).closest(".popup").removeClass("on");
  });

  // video 
  $(".iconPlay").on("click",function(){
    $(this).closest(".viderArea").find("video").get(0).play();
    $(this).closest(".viderArea").removeClass("playOff");
    $(this).closest(".viderArea").addClass("playOn");
  });
  $(".iconPause").on("click",function(){
    $(this).closest(".viderArea").find("video").get(0).pause();
    $(this).closest(".viderArea").removeClass("playOn");
    $(this).closest(".viderArea").addClass("playOff");
  });

  // chatbot write
  $(".chatbotWriteWrap .textAreaBox").on("focus", function(){
    $(this).addClass("on");
    $(".iconChatSpeak").hide();
    $(".iconChatTyping").show();
  });
  $(".iconChatTyping").on("click",function(){
    $(this).hide();
    $(".iconChatSpeak").show();
    $(this).closest(".chatbotWriteWrap").find(".textAreaBox").val("");
    $(this).closest(".chatbotWriteWrap").find(".textAreaBox").removeClass("on");
  });
  $(".iconChatSpeak").on("click",function(){
    if($(this).hasClass("on")) {
      $(this).removeClass("on")
    } else {
      $(this).addClass("on");      
    }
  });

  // device check
  const isMobile = /iPhone|iPad|iPod|Android/i.test(window.navigator.userAgent);
  if (isMobile) {
    $(".modalChatbot").addClass("deviceMobile");
  } else {
    $(".modalChatbot").addClass("devicePC");
  }

  // chatbot open
  $(".btnChatbot").on("click", function(){
    if($(".btnChatbot").hasClass("on")) {
      $(".btnChatbot").removeClass("on");
      $(".modalChatbot").removeClass("on");
    } else {
      $(this).addClass("on");
    }
  });
});