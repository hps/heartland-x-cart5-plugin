<div class="securesubmit configured">

  <div IF="paymentMethod.getReferralPageURL()" class="note">
    {t(#Don't have an account?#)}
    <span class="external"><a href="{paymentMethod.getReferralPageURL()}" target="_blank">{t(#Sign Up Now#)}</a> <i class="icon fa fa-external-link"></i></span>
  </div>

  <table cellspacing="1" cellpadding="5" class="settings-table">

    <tr>
      <td class="setting-name"><label for="settings_publicKey">{t(#Public key#)}</label></td>
      <td>
        <input type="text" id="settings_prefix" value="{paymentMethod.getSetting(#publicKey#)}" name="settings[publicKey]" />
      </td>
    </tr>

    <tr>
      <td class="setting-name"><label for="settings_secretKey">{t(#Secret key#)}</label></td>
      <td>
        <input type="text" id="settings_prefix" value="{paymentMethod.getSetting(#secretKey#)}" name="settings[secretKey]" />
      </td>
    </tr>

    <tr>
      <td class="setting-name"><label for="settings_type">{t(#Transaction type#)}</label></td>
      <td>
      <select id="settings_type" name="settings[type]" class="form-control">
        <option value="sale" selected="{isSelected(paymentMethod.getSetting(#type#),#sale#)}">{t(#Authorization and Capture#)}</option>
        <option value="auth" selected="{isSelected(paymentMethod.getSetting(#type#),#auth#)}">{t(#Authorization only#)}</option>
      </select>
      </td>
    </tr>

    <tr>
      <td class="setting-name"><label for="settings_prefix">{t(#Invoice number prefix#)}</label></td>
      <td><input type="text" id="settings_prefix" value="{paymentMethod.getSetting(#prefix#)}" name="settings[prefix]" /></td>
    </tr>

    <tr>
      <td class="setting-name"><label for="settings_useSavedCards">{t(#Allow saved cards#)}</label></td>
      <td>
      <select id="settings_useSavedCards" name="settings[useSavedCards]" class="form-control">
        <option value="no" selected="{isSelected(paymentMethod.getSetting(#useSavedCards#),#no#)}">{t(#No#)}</option>
        <option value="yes" selected="{isSelected(paymentMethod.getSetting(#useSavedCards#),#yes#)}">{t(#Yes#)}</option>
      </select>
      </td>
    </tr>

    <tr>
      <td class="setting-name"><label for="settings_useAdvancedFraud">{t(#Enable Advanced Fraud#)}</label></td>
      <td>
      <select id="settings_useAdvancedFraud" name="settings[useAdvancedFraud]" class="form-control">
        <option value="no" selected="{isSelected(paymentMethod.getSetting(#useAdvancedFraud#),#no#)}">{t(#No#)}</option>
        <option value="yes" selected="{isSelected(paymentMethod.getSetting(#useAdvancedFraud#),#yes#)}">{t(#Yes#)}</option>
      </select>
      </td>
    </tr>

    <tr>
      <td class="setting-name"><label for="settings_emailStoreOwner">{t(#Email Store Owner of Suspected Fraud#)}</label></td>
      <td>
      <select id="settings_emailStoreOwner" name="settings[emailStoreOwner]" class="form-control">
        <option value="no" selected="{isSelected(paymentMethod.getSetting(#emailStoreOwner#),#no#)}">{t(#No#)}</option>
        <option value="yes" selected="{isSelected(paymentMethod.getSetting(#emailStoreOwner#),#yes#)}">{t(#Yes#)}</option>
      </select>
      </td>
    </tr>

    <tr>
      <td class="setting-name"><label for="settings_notificationEmail">{t(#Notification Email Address#)}</label></td>
      <td>
        <input type="text" id="settings_prefix" value="{paymentMethod.getSetting(#notificationEmail#)}" name="settings[notificationEmail]" />
      </td>
    </tr>

    <tr>
      <td class="setting-name"><label for="settings_fraudText">{t(#Fraud Text#)}</label></td>
      <td>
        <input type="text" id="settings_prefix" value="{paymentMethod.getSetting(#fraudText#)}" name="settings[fraudText]" />
      </td>
    </tr>

    <tr>
      <td>&nbsp;</td>
      <td>
        <div class="buttons">
          <widget class="\XLite\View\Button\Submit" label="{t(#Update#)}" style="regular-main-button" />
        </div>
      </td>
    </tr>

  </table>

</div>
