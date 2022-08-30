/**
* Aerojet Rocketdyne AFA Virtual Booth
**********************************************************************
	Class: AFAVirtualBooth
	Description: AFA Virtual Booth interactive.
**********************************************************************
* 
* @author Redmon Group, Inc
* @link http.//www.redmon.com/
* @version 1
* @since 0.1
**********************************************************************
*/
let AFAVirtualBooth = function () {

  const ANIMATION_END = "animationend webkitAnimationEnd oAnimationEnd MSAnimationEnd";
  const CLASS_PRODUCT_TARGET = "product-target";
  const BUTTON_STATE_ENTER = "enter";
  const BUTTON_STATE_LEAVE = "leave";
  const BUTTON_STATE_OPEN = "open";
  const BUTTON_STATE_CLOSE = "close";

  let _root_info = {}, _products_info = {}, _targets_info = {};

  /**
   * Constructor: AFAVirtualBooth
   */
  function constructor() {

    try {

      _root_info.isMobile = false;
      _root_info.isIE = ieCheck();
      _root_info.interactiveWidth = parseInt(_root_info.isIE ? ieGetStyle("#main-content", "width") : window.getComputedStyle(document.body).getPropertyValue("--interactive-width"));
      _root_info.interactiveHeight = parseInt(_root_info.isIE ? ieGetStyle("#main-content", "height") : window.getComputedStyle(document.body).getPropertyValue("--interactive-height"));

      $(window).resize(updateProjector);
      updateProjector();

      // Loads the products and background image from the JSON file.
      let json_xhr = new XMLHttpRequest();
      json_xhr.overrideMimeType("application/json");
      json_xhr.open("GET", "json/afa_virtual_booth.json");
      json_xhr.onreadystatechange = function () {
        if( json_xhr.readyState == 4 && json_xhr.status == "200" ) {
          let json_info = JSON.parse(json_xhr.responseText);
          $("#background-image").append($('<img src="img/' + json_info.background.image_ref + '">'));
          _products_info = json_info.products;
          products_setup();
        }
      };
      json_xhr.send(null);
    } catch (error) {
      console.error("<<AFAVirtualBooth constructor>> ERROR: " + error);
    }
  }

  /**
   * Function: products_setup
   */
  function products_setup() {

    //console.log("<<AFAVirtualBooth products_setup>> --is-mobile: " + window.getComputedStyle(document.body).getPropertyValue("--is-mobile"));

    _root_info.contentBody = $("#main-content");
    _root_info.popupsContainer = $("#popups-container");
    _root_info.mobileContainer = $("#mobile-container");
    _root_info.buttonBlocker = $("#button-blocker");
    _root_info.productTargetsContainer = $("#" + CLASS_PRODUCT_TARGET + "s-container");
    _targets_info.targets_array = [];

    // Sets up products.
    for (product_index in _products_info) {

      //console.log("<<AFAVirtualBooth products_setup>> product_index: " + product_index);

      // Sets up product.
      let currProduct = _products_info[product_index];
      let currTargetBtn_info = currProduct.target_button;
      if( currTargetBtn_info != null ) {

        //console.log("<<AFAVirtualBooth products_setup>> image_ref: " + currProduct.target_button.image_ref);

        // Sets up button.
        $.get("img/" + currTargetBtn_info.image_ref, function (data) {

          let currTarget_btn = $(data.documentElement);
          let currTargetContainer = $('<div />');
          _root_info.productTargetsContainer.append(currTargetContainer);
          let currLabelContainer = $('<div class="product-target-label-container" />');
          currTarget_btn.data("labelContainer", currLabelContainer);
          currLabelContainer.css("left", currTargetBtn_info.loc_x);
          currLabelContainer.css("top", currTargetBtn_info.loc_y);
          currTargetContainer.append(currLabelContainer);
          let currLabelText = $('<span />');
          currTarget_btn.data("labelText", currLabelText);
          currLabelContainer.append(currLabelText);
          let currTargetBtn_index = _products_info.indexOf(currProduct);
          currTarget_btn.data("index", currTargetBtn_index);
          currTarget_btn.attr("class", CLASS_PRODUCT_TARGET + "-content");
          let style_loc = "left: " + currTargetBtn_info.loc_x + "; top: " + currTargetBtn_info.loc_y + ";";
          currTarget_btn.attr("style", style_loc);
          currTargetContainer.append(currTarget_btn);

          //console.log("<<AFAVirtualBooth products_setup>> LOADED product_index: " + product_index + "  title: " + currProduct.title);

          currTarget_btn.find("g").each(function (g_index, currG) {

            try {
                
              currGroup = $(currG);
              if( currGroup.attr("id") == "hitspot_mc" ) {

                currGroup.attr("class", CLASS_PRODUCT_TARGET);
                currGroup.attr("class", CLASS_PRODUCT_TARGET + "-hover");
                currGroup.on("mouseenter", onProductButtonStateChange(BUTTON_STATE_ENTER));
                currGroup.on("click", onProductButtonStateChange(BUTTON_STATE_OPEN));
                currGroup.data("target", currTarget_btn);
                _targets_info.targets_array[currTargetBtn_index] = currTarget_btn;

                // Sets up the label.
                currLabelText.html(currProduct.title);
                let label_width = currLabelContainer.outerWidth();
                currLabelContainer.css("width", label_width - 10 + "px");
                currLabelContainer.css("margin-left", -Math.abs((label_width / 2) - (currTarget_btn.outerWidth() / 2)) + "px");
              }
            } catch (error) {
              console.error("<<AFAVirtualBooth products_setup>> ERROR: " + error);
            }
          });
        });
      }

      // Creates the pop-up.
      let currPopup = $('<div id="popup-' + product_index + '" class="popup">' +
        '<div id="popup-title-' + product_index + '" class="popup-title">' + currProduct.title + '</div>' +
        '<div id="popup-divider-' + product_index + '" class="popup-divider"></div>' +
        '<div id="popup-button-menu-' + product_index + '" class="popup-button-menu"></div>' +
        '<div id="popup-contents-container-' + product_index + '" class="popup-content"></div>' +
        '<div id="popup-captions-container-' + product_index + '" class="popup-caption"></div>' +
        '<button id="popup-button-close-' + product_index + '" class="popup-button popup-button-close">CLOSE</button>' +
        '</div>'
      );
      currPopup.find("#popup-button-close-" + product_index).on("click", onProductButtonStateChange(BUTTON_STATE_CLOSE));

      // For mobile.
      let currMobile_btn = $('<div id="mobile-product-button-' + product_index + '" class="mobile-product-button">' +
        '<div id="mobile-product-title-' + product_index + '" class="popup-title">' + currProduct.title + '</div>' +
        '<div id="mobile-product-divider-' + product_index + '" class="popup-divider"></div>' +
        '<div id="mobile-container-' + product_index + '" class="mobile-contents-container">' +
        '<div id="mobile-contents-container-' + product_index + '" class="popup-content"></div>' +
        '<div id="mobile-captions-container-' + product_index + '" class="popup-caption"></div>' +
        '<div id="mobile-menu-buttons-' + product_index + '" class="popup-button-menu"></div>' +
        '<div class="mobile-button-close-container">' +
        '<button id="mobile-button-close-' + product_index + '" class="popup-button popup-button-close">CLOSE</button>' +
        '</div>' +
        '</div>' +
        '</div>'
      );
      let currMobileContainer = currMobile_btn.find("#mobile-container-" + product_index);
      currMobileContainer.data("index", product_index);
      currMobile_btn.data("container", currMobileContainer);
      let close_btn = currMobile_btn.find("#mobile-button-close-" + product_index);
      close_btn.data("mobileProduct_btn", currMobile_btn);
      currMobile_btn.data("close_btn", close_btn);
      close_btn.on("click", onProductButtonStateChange(BUTTON_STATE_CLOSE));
      currMobile_btn.find("#mobile-product-title-" + product_index).on("click", onProductButtonStateChange(BUTTON_STATE_OPEN));

      // Sets up the product menu buttons.
      let hasButtons = false;
      let button_width = 0;
      currProduct.buttons ? button_width = window.getComputedStyle(document.body).getPropertyValue("--button-width-" + currProduct.buttons.length) : null;
      (button_width == "") ? button_width = ieGetStyle("#root-button-width-" + currProduct.buttons.length, "width"): null;

      for (button_index in currProduct.buttons) {

        try {

          hasButtons = true;
          let currButton_info = currProduct.buttons[button_index];
          let currMenu_btn = $('<button id="popup-menu-button-' + button_index + '" class="popup-button' + ((button_index == 0) ? ' popup-button-active' : '') + '">' + currButton_info.label + '</button>');
          currMenu_btn.data("index", button_index);
          currMenu_btn.css("width", button_width);
          currPopup.find("#popup-button-menu-" + product_index).append(currMenu_btn);
          let currContent = $('<div id="popup-content-' + button_index + '" style="display: ' + ((button_index == 0) ? 'block' : 'none') + '"></div>');

          // For mobile.
          let currMobileMenu_btn = $('<button id="mobile-menu-button-' + button_index + '" class="popup-button' + ((button_index == 0) ? ' popup-button-active' : '') + '">' + currButton_info.label.replace(/<br>/gi, " ") + '</button>');
          currMobileMenu_btn.data("index", button_index);
          currMobile_btn.find("#mobile-menu-buttons-" + product_index).append(currMobileMenu_btn);
          let currMobileContent = $('<div id="mobile-content-' + button_index + '" style="display: ' + ((button_index == 0) ? 'block' : 'none') + '"></div>');

          // Sets up content.
          switch (currButton_info.type.toLowerCase()) {
            case "text":
              currContent.html(currButton_info.content);
              currMenu_btn.on("click", onProductContentChange);
              currMobileContent.html(currButton_info.content);
              currMobileMenu_btn.on("click", onProductContentChange);
              break;

            case "link":
              currMenu_btn.on("click", function () {
                window.open(currButton_info.content)
              });
              currMobileMenu_btn.on("click", function () {
                window.open(currButton_info.content)
              });
              break;

            case "video":
              currContent.html('<iframe class="popup-video" src="' + currButton_info.content + '" frameborder="0"></iframe>');
              currMenu_btn.on("click", onProductContentChange);
              currMobileContent.html('<iframe class="popup-video" src="' + currButton_info.content + '" frameborder="0"></iframe>');
              currMobileMenu_btn.on("click", onProductContentChange);
              break;
          }
          currPopup.find("#popup-contents-container-" + product_index).append(currContent);
          if (currButton_info.caption) {

            currPopup.find("#popup-captions-container-" + product_index).append($('<div id="popup-content-caption-' + button_index + '" style="display: ' + ((button_index == 0) ? 'block' : 'none') + '">' + currButton_info.caption + '</div>'));
            currMobile_btn.find("#mobile-captions-container-" + product_index).append($('<div id="mobile-content-caption-' + button_index + '" style="display: ' + ((button_index == 0) ? 'block' : 'none') + '">' + currButton_info.caption + '</div>'));
          }
          currMobile_btn.find("#mobile-contents-container-" + product_index).append(currMobileContent);

          //console.log("<<AFAVirtualBooth products_setup>> button_index: " + button_index + "  label: " + currButton_info.label);
        } catch (error) {
          console.error("<<AFAVirtualBooth products_setup>> ERROR: " + error);
        }
      }
      currPopup.data("hasButtons", hasButtons);
      currPopup.data("url", currProduct.url);
      currPopup.data("index", product_index);
      currMobile_btn.data("hasButtons", hasButtons);
      currMobile_btn.data("url", currProduct.url);
      currMobile_btn.data("index", product_index);
      _root_info.popupsContainer.append(currPopup);
      _root_info.mobileContainer.append(currMobile_btn);

      //console.log("<<AFAVirtualBooth products_setup>> left: " + currPopup.css("left") + "  top: " + currPopup.css("top"));
      //console.log("<<AFAVirtualBooth products_setup>> left: " + currPopup[0].getBBox().x + "  top: " + currPopup[0].getBBox().y);
    }
  }

  /**
   * Function: onResetLastTarget
  function onResetLastTarget() {
    try {

      //console.log("<<AFAVirtualBooth onResetLastTarget>>");

      _root_info.popupsContainer.data("prevPath") ? _root_info.popupsContainer.data("prevPath").attr("class", CLASS_PRODUCT_TARGET) : null;
    } catch (error) {
      console.error("<<AFAVirtualBooth onResetLastTarget>> ERROR: " + error);
    }
  }
   */

  /**
   * Function: onProductButtonStateChange
   */
  function onProductButtonStateChange(stateType) {

    return function (event) {

      try {

        //console.log("<<AFAVirtualBooth onProductButtonStateChange>> stateType: " + stateType);

        let currGroup = $(this);
        _root_info.isMobile ? (currGroup = (currGroup.attr("id").indexOf("mobile-button-close-") == -1) ? currGroup.parent() : currGroup.data("mobileProduct_btn")) : null;
        let currTarget_btn = (stateType != BUTTON_STATE_CLOSE) ? currGroup.data("target") : null;
        let currPopup, currLabelContainer, currLabelText;
        switch (stateType) {
          case BUTTON_STATE_ENTER:

            currGroup.off("mouseenter");
            currLabelContainer = currTarget_btn.data("labelContainer");
            currLabelContainer.off(ANIMATION_END).removeClass("product-target-label-bkgd-off").addClass("product-target-label-bkgd-on");
            currTarget_btn.data("labelText").removeClass("product-target-label-text-off").addClass("product-target-label-text-on");
            currLabelContainer.one(ANIMATION_END, function(event) {
              currLabelContainer.off(ANIMATION_END);
            });
            currGroup.on("mouseleave", onProductButtonStateChange(BUTTON_STATE_LEAVE));
            break;

          case BUTTON_STATE_LEAVE:

            currGroup.off("mouseleave");
            currLabelContainer = currTarget_btn.data("labelContainer");
            currLabelContainer.off(ANIMATION_END).removeClass("product-target-label-bkgd-on").addClass("product-target-label-bkgd-off");
            currLabelText = currTarget_btn.data("labelText");
            currLabelText.removeClass("product-target-label-text-on").addClass("product-target-label-text-off");
            currLabelContainer.one(ANIMATION_END, function(event) {
              currLabelContainer.off(ANIMATION_END).removeClass("product-target-label-bkgd-off").removeClass("product-target-label-bkgd-on");
              currLabelText.removeClass("product-target-label-text-on").removeClass("product-target-label-text-off");
            });
            currGroup.on("mouseenter", onProductButtonStateChange(BUTTON_STATE_ENTER));
            break;

          case BUTTON_STATE_OPEN:
            if (_root_info.isMobile) {

              let prevGroup = _root_info.mobileContainer.data("prevGroup");
              try {
                (prevGroup != null && prevGroup.attr("id") != currGroup.attr("id")) ? prevGroup.data("close_btn").trigger("click"): null;
              } catch (error) {}
              if (currGroup.data("container").css("display") == "none") {

                popupReset(currGroup);
                if (currGroup.data("hasButtons")) {

                  _root_info.mobileContainer.data("prevGroup", currGroup);
                  currGroup.attr("class", "mobile-product-button-active");
                  currGroup.data("container").css("display", "block");
                  currGroup.find("#mobile-menu-button-0").trigger("click");
                }
              } else {

                currGroup.data("close_btn").trigger("click");
              }
              currGroup ? (currGroup.data("url") ? window.open(currGroup.data("url")) : null) : null;
              try {
                currGroup[0].scrollIntoView({
                  behavior: "smooth",
                  block: "start",
                  inline: "start"
                });
              } catch (error) {}
            } else {

              currPopup = _root_info.popupsContainer.find("#popup-" + currTarget_btn.data("index"));
              if (currPopup.data("hasButtons")) {

                currPopup.css("display", "block");
                _root_info.buttonBlocker.css("display", "block");
                currPopup.css("opacity", 0);
                currPopup.stop();
                currPopup.data("isFadingIn", true);
                currPopup.fadeTo("slow", 1);
              }
              currPopup ? (currPopup.data("url") ? window.open(currPopup.data("url")) : null) : null;
            }
            break;

          case BUTTON_STATE_CLOSE:
            if (_root_info.isMobile) {

              _root_info.mobileContainer.data("prevGroup", null);
              popupReset(currGroup);
              currGroup.data("container").css("display", "none");
              currGroup.attr("class", "mobile-product-button");
            } else {
              currPopup = _root_info.popupsContainer.find("#popup-" + currGroup.parent().data("index"));
              currPopup.stop();
              currPopup.data("isFadingIn", true);
              currPopup.fadeTo("slow", 0, function () {
                currPopup.css("display", "none");
              });
              _root_info.buttonBlocker.css("display", "none");
              popupReset(currPopup);
              currPopup.find("#popup-menu-button-0").attr("class", "popup-button popup-button-active");
              currPopup.find("#popup-content-0").css("display", "block");
            }
            break;
        }
      } catch (error) {
        console.error("<<AFAVirtualBooth onProductButtonStateChange>> ERROR: " + error);
      }
    }
  }

  /**
   * Function: onProductContentChange
   */
  function onProductContentChange(event) {

    try {

      let currMenu_btn = $(this);
      let curr_index = currMenu_btn.data("index");
      let currPopup = currMenu_btn.parent().parent();
      let contentType = "popup";
      if (_root_info.isMobile) {

        contentType = "mobile";
        currPopup.find("#mobile-contents-container-" + currPopup.parent().data("index")).children().each(function (content_index, currContent) {

          //console.log("<<AFAVirtualBooth onProductContentChange>> content_index: " + content_index + "  currContent: " + currContent);

          currContent = $(currContent);
          (currContent.css("display") == "block") ? currContent.css("display", "none"): null;
        });
      }
      popupReset(currPopup);
      currPopup.find("#" + contentType + "-menu-button-" + curr_index).attr("class", "popup-button popup-button-active");
      currPopup.find("#" + contentType + "-content-" + curr_index).css("display", "block");
      currPopup.find("#" + contentType + "-content-caption-" + curr_index).css("display", "block");
    } catch (error) {
      console.error("<<AFAVirtualBooth onProductContentChange>> ERROR: " + error);
    }
  }

  /**
   * Function: popupReset
   */
  function popupReset(currPopup) {

    try {

      //console.log("<<AFAVirtualBooth popupReset>> isMobile: " + _root_info.isMobile + "  currPopup: " + currPopup.attr("id"));

      let contentType = _root_info.isMobile ? "mobile" : "popup";
      currPopup.find("button").each(function (button_index, curr_btn) {

        curr_btn = $(curr_btn);
        (curr_btn.attr("class") == "popup-button popup-button-active") ? curr_btn.attr("class", "popup-button"): null;
      });

      let curr_index = currPopup.data("index");
      currPopup.find("#" + contentType + "-contents-container-" + curr_index).children().each(function (div_index, currDiv) {

        currDiv = $(currDiv);
        (currDiv.css("display") == "block") ? currDiv.css("display", "none"): null;
        currDiv.find(".popup-video").each(function (video_index, currVideo) { // Stops all video.
          let currSrc = $(currVideo).attr("src");
          $(currVideo).attr("src", currSrc);
        });
      });

      currPopup.find("#" + contentType + "-captions-container-" + curr_index).children().each(function (caption_index, currCaption) {

        currCaption = $(currCaption);
        (currCaption.css("display") == "block") ? currCaption.css("display", "none"): null;
      });
    } catch (error) {
      console.error("<<AFAVirtualBooth popupReset>> ERROR: " + error);
    }
  }

  /*************************************************************************************************/
  /*****                                   SUPPORT FUNCTIONS                                   *****/
  /*************************************************************************************************/

  /**
   * Function: updateProjector
   */
  function updateProjector() {

    try {

      //console.log("<<AFAVirtualBooth updateProjector>> isMobile: " + _root_info.isMobile);

      // Resizes and centers the interactive.
      let currScale = Math.min(window.innerWidth / _root_info.interactiveWidth, window.innerHeight / _root_info.interactiveHeight);
      $("#main-content").css({
        transform: "translate(" + (-50 * currScale) + "%, " + (-50 * currScale) + "%) " + "scale(" + currScale + ")"
      });
      _root_info.isMobile = window.getComputedStyle(document.body).getPropertyValue("--is-mobile").trim().toLowerCase() === "true";
    } catch (error) {
      console.error("<<AFAVirtualBooth updateProjector>> ERROR: " + error);
    }
  }

  /**
   * Function: ieCheck
   */
  function ieCheck(asVersion) {

    (asVersion == null) ? asVersion = false: null;
    let return_version = -1;
    if (navigator.appName == 'Microsoft Internet Explorer') { // IE below 11.

      let userAgent = navigator.userAgent;
      let msie_re = new RegExp("MSIE ([0-9]{1,}[\\.0-9]{0,})");

      (msie_re.exec(userAgent) !== null) ? return_version = parseFloat(RegExp.$1): null;
    } else if (navigator.appName == "Netscape") { // All other browsers.                  

      return_version = (navigator.appVersion.indexOf('Trident') === -1) ? 12 : 11; // IE 11 check.
    }
    return ((asVersion) ? return_version : (return_version <= 11));
  }

  /**
   * Function: ieGetStyle
   */
  function ieGetStyle(className, propertyName) {

    let propertyValue = "";
    try {

      let classes_info = document.styleSheets[0].rules || document.styleSheets[0].cssRules;
      for (let class_index in classes_info) {

        let currClass_info = classes_info[class_index];
        if (currClass_info.selectorText == className) {
          propertyValue += currClass_info.cssText || currClass_info.style.cssText;
          try {
            (propertyName != null) ? propertyValue = currClass_info.style.getPropertyValue(propertyName): null;
          } catch (error) {}
        }
      }
    } catch (error) {
      console.error("<<AFAVirtualBooth ieGetStyle>> ERROR: " + error);
    }
    return (propertyValue);
  }

  constructor();
};

/*************************************************************************************************/
/*****                                  STARTING FUNCTIONS                                   *****/
/*************************************************************************************************/

const SECOND = 1000;
let _afaVirtualBooth;
document.onreadystatechange = function (event) {

  //console.log("<<AFAVirtualBooth onreadystatechange>> document.readyState: " + document.readyState);

  switch (document.readyState) {

    case "interactive":
      // Resizes and centers the interactive.
      let currScale = Math.min(window.innerWidth / parseInt(window.getComputedStyle(document.body).getPropertyValue("--interactive-width")), window.innerHeight / parseInt(window.getComputedStyle(document.body).getPropertyValue("--interactive-height")));
      $("#main-content").css({
        transform: "translate(" + (-50 * currScale) + "%, " + (-50 * currScale) + "%) " + "scale(" + currScale + ")"
      });
      break;

    case "complete":
      break;
  }
};

window.onload = function afaVirtualBooth_init() {

  _afaVirtualBooth = new AFAVirtualBooth();

  let loadingScreen = $("#loading-screen");
  loadingScreen.fadeOut(3 * SECOND);
};