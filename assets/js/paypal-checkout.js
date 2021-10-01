!function(e){var t={};function a(n){if(t[n])return t[n].exports;var r=t[n]={i:n,l:!1,exports:{}};return e[n].call(r.exports,r,r.exports,a),r.l=!0,r.exports}a.m=e,a.c=t,a.d=function(e,t,n){a.o(e,t)||Object.defineProperty(e,t,{enumerable:!0,get:n})},a.r=function(e){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})},a.t=function(e,t){if(1&t&&(e=a(e)),8&t)return e;if(4&t&&"object"==typeof e&&e&&e.__esModule)return e;var n=Object.create(null);if(a.r(n),Object.defineProperty(n,"default",{enumerable:!0,value:e}),2&t&&"string"!=typeof e)for(var r in e)a.d(n,r,function(t){return e[t]}.bind(null,r));return n},a.n=function(e){var t=e&&e.__esModule?function(){return e.default}:function(){return e};return a.d(t,"a",t),t},a.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},a.p="",a(a.s=83)}({0:function(e,t){e.exports=jQuery},83:function(e,t,a){(function(e){var t={isMounted:!1,init:function(){document.getElementById("edd-paypal-container")&&this.initButtons("#edd-paypal-container","checkout"),e(document.body).on("edd_discount_applied",this.maybeRefreshPage),e(document.body).on("edd_discount_removed",this.maybeRefreshPage)},isPayPal:function(){var t=!1;return e("select#edd-gateway, input.edd-gateway").length&&(t=e("meta[name='edd-chosen-gateway']").attr("content")),!t&&edd_scripts.default_gateway&&(t=edd_scripts.default_gateway),"paypal_commerce"===t},maybeRefreshPage:function(e,a){(0===a.total_plain&&t.isPayPal()||!t.isMounted&&t.isPayPal()&&a.total_plain>0)&&window.location.reload()},setErrorHtml:function(t,a,n){if("checkout"===a&&"undefined"!=typeof edd_global_vars&&edd_global_vars.checkout_error_anchor)(r=document.getElementById("edd-paypal-errors-wrap"))&&(r.innerHTML=n);else if("buy_now"===a){var r,o=t.closest(".edd_download_purchase_form");(r=!!o&&o.querySelector(".edd-paypal-checkout-buy-now-error-wrapper"))&&(r.innerHTML=n)}e(document.body).trigger("edd_checkout_error",[n])},initButtons:function(e,a){t.isMounted=!0,paypal.Buttons(t.getButtonArgs(e,a)).render(e),document.dispatchEvent(new CustomEvent("edd_paypal_buttons_mounted"))},getButtonArgs:function(a,n){var r="checkout"===n?document.getElementById("edd_purchase_form"):a.closest(".edd_download_purchase_form"),o="checkout"===n?r.querySelector("#edd-paypal-errors-wrap"):r.querySelector(".edd-paypal-checkout-buy-now-error-wrapper"),d="checkout"===n?document.getElementById("edd-paypal-spinner"):r.querySelector(".edd-paypal-spinner"),u=r.querySelector('input[name="edd_process_paypal_nonce"]'),i=r.querySelector('input[name="edd-process-paypal-token"]'),c="subscription"===eddPayPalVars.intent?"createSubscription":"createOrder",s={onApprove:function(e,r){var o=new FormData;return o.append("action",eddPayPalVars.approvalAction),o.append("edd_process_paypal_nonce",u.value),o.append("token",i.getAttribute("data-token")),o.append("timestamp",i.getAttribute("data-timestamp")),e.orderID&&o.append("paypal_order_id",e.orderID),e.subscriptionID&&o.append("paypal_subscription_id",e.subscriptionID),fetch(edd_scripts.ajaxurl,{method:"POST",body:o}).then((function(e){return e.json()})).then((function(e){if(e.success&&e.data.redirect_url)window.location=e.data.redirect_url;else{d.style.display="none";var o=e.data.message?e.data.message:eddPayPalVars.defaultError;if(t.setErrorHtml(a,n,o),e.data.retry)return r.restart()}}))},onError:function(e){d.style.display="none",e.name="",t.setErrorHtml(a,n,e)},onCancel:function(e){d.style.display="none"}};return eddPayPalVars.style&&(s.style=eddPayPalVars.style),s[c]=function(t,a){return d.style.display="block",o&&(o.innerHTML=""),fetch(edd_scripts.ajaxurl,{method:"POST",body:new FormData(r)}).then((function(e){return e.json()})).then((function(t){if(t.data&&t.data.paypal_order_id)return t.data.nonce&&(u.value=t.data.nonce),t.data.token&&(e(i).attr("data-token",t.data.token),e(i).attr("data-timestamp",t.data.timestamp)),t.data.paypal_order_id;var a=eddPayPalVars.defaultError;return t.data&&"string"==typeof t.data?a=t.data:"string"==typeof t&&(a=t),new Promise((function(e,t){t(a)}))}))},s}};e(document.body).on("edd_gateway_loaded",(function(e,a){"paypal_commerce"===a&&t.init()})),e(document).ready((function(e){for(var a=document.querySelectorAll(".edd-paypal-checkout-buy-now"),n=0;n<a.length;n++){var r=a[n];if(!r.classList.contains("edd-free-download")){var o=r.closest(".edd_purchase_submit_wrapper");if(o){o.innerHTML="";var d=document.createElement("div");d.classList.add("edd-paypal-checkout-buy-now-error-wrapper"),o.before(d);var u=document.createElement("span");u.classList.add("edd-paypal-spinner","edd-loading-ajax","edd-loading"),u.style.display="none",o.after(u),t.initButtons(o,"buy_now")}}}}))}).call(this,a(0))}});