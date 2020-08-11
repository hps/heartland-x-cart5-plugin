core.bind(
  'checkout.main.initialize',
  function() {
    var handler = null;

    core.bind(
      'checkout.paymentTpl.postprocess',
      function(event, data) {
        var myselect = document.getElementById("card-expiry-year"), year = new Date().getFullYear();
        if (myselect)
          var gen = function(max){do{myselect.add(new Option(year++),null);}while(max-->0);}(10);

        var heartlandContainer = jQuery('.heartland-container');

        if (heartlandContainer.length && !handler) {
          handler = hps;

          PaymentTplView.prototype.handleUpdateCartSecuresubmit = function (event, data)
          {
            if (!this.isLoading && 'undefined' != typeof(data.total)) {
              this.load();
            }
          }

          core.bind(
            'updateCart',
            _.bind(data.widget.handleUpdateCartSecuresubmit, data.widget)
          );
        }
      }
    );

    core.bind(
      'checkout.common.ready',
      function(event, state) {
        var heartlandContainer = jQuery('.heartland-container');

        if (handler &&
            heartlandContainer.length &&
            (heartlandContainer.find('[name="payment[securesubmit_use_stored_card]"]:checked').length === 0 || 
             heartlandContainer.find('[name="payment[securesubmit_use_stored_card]"]:checked').val() === 'new') &&
            !heartlandContainer.find('#securesubmit_token').val()
        ) {
          var card  = jQuery('#card-number').val().replace(/\D/g, '');
          var cvc   = jQuery('#card-cvc').val();
          var month = jQuery('#card-expiry-month').val();
          var year  = jQuery('#card-expiry-year').val();

          hps.tokenize({
            data: {
              public_key: heartlandContainer.data('key'),
              number: card,
              cvc: cvc,
              exp_month: month,
              exp_year: year
            },
            success: function(response) {
              $('#securesubmit_token').val(response.token_value);
              $('#securesubmit_card_type').val(response.card_type);
              $('#securesubmit_last_four').val(card.substr(-4));
              $('#securesubmit_exp_month').val(response.exp_month);
              $('#securesubmit_exp_year').val(response.exp_year);
              jQuery('body').css('overflow', 'visible');
              jQuery('form.place').submit();
            },
            error: function(response) {
              alert(response.message);
            }
          });

          state.state = false;
        }
      }
    );
  }
);
