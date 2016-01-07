<p class="heartland-cards-description">
  No actual credit card details are stored on this site.Customer
</p>

{if:profile.getSecuresubmitCreditCards()}
  <widget class="\XLite\Module\Heartland\Securesubmit\View\Form\SecuresubmitCreditCards" name="securesubmitcreditcards" />
    <input type="hidden" name="delete_token" id="heartland-delete-token" />

    <h2>Customer's Stored Cards</h2>
    <table>
      <tr>
        <th>Customer Card</th>
        <th>Actions</th>
      </tr>
      {foreach:profile.getSecuresubmitCreditCards(),i,cc}
      <tr>
        <td>
          {cc.cardBrand}
          ending with *{cc.lastFour}
          ({cc.expMonth}/{cc.expYear})
        </td>
        <td>
          <a href="javascript:void(0);" onclick="$('#heartland-delete-token').val('{cc.id}'); $(this).closest('form').submit();">
            Delete
          </a>
        </td>
      </tr>
      {end:}
    </table>
  <widget name="securesubmitcreditcards" end />
{else:}
  <div class="alert alert-warning">
    <strong>{t(#Note!#)}</strong>
    {t(#No credit cards associated with this profile.#)}
  </div>
{end:}
