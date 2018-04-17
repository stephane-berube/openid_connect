<?php

/**
 * @file
 * Documentation for OpenID Connect module APIs.
 */

use Drupal\user\UserInterface;

/**
 * Modify the list of claims.
 *
 * @param array $claims
 *   A array of claims.
 *
 * @ingroup openid_connect_api
 */
function hook_openid_connect_claims_alter(array &$claims) {
  $claims['custom_claim'] = [
    'scope' => 'profile',
    'title' => 'Custom Claim',
    'type' => 'string',
    'description' => 'A custom claim from provider',
  ];
}

/**
 * Alter hook to alter OpenID Connect client plugins.
 *
 * @param array $client_info
 *   An array of client information.
 *
 * @ingroup openid_connect_api
 */
function hook_openid_connect_openid_connect_client_info_alter(array &$client_info) {
  $client_info['generic'] = [
    'id' => 'generic',
    'label' => [
      'string' => 'Generic',
      'translatableMarkup' => NULL,
      'options' => [],
      'stringTranslation' => NULL,
      'arguments' => [],
    ],
    'class' => 'Drupal\openid_connect\Plugin\OpenIDConnectClient\Generic',
    'provider' => 'openid_connect',
  ];
}

/**
 * Alter hook to alter the user properties to be skipped for mapping.
 *
 * @param array $properties_to_skip
 *   An array of of properties to skip.
 *
 * @ingroup openid_connect_api
 */
function hook_openid_connect_user_properties_to_skip_alter(array &$properties_to_skip) {
  // Allow to map the username to a property from the provider.
  unset($properties_to_skip['name']);
}

/**
 * Alter hook to alter userinfo before authorization or connecting a user.
 *
 * @param array $userinfo
 *   An array of returned user information.
 * @param array $context
 *   - user_data: An array of user_data.
 */
function hook_openid_connect_userinfo_alter(array &$userinfo, array $context) {
}

/**
 * Post authorize hook that runs after the user logged in via OpenID Connect.
 *
 * @param array $tokens
 *   An array of tokens.
 * @param \Drupal\user\UserInterface $account
 *   A user account object.
 * @param array $userinfo
 *   An array of user information.
 * @param string $plugin_id
 *   The plugin identifier.
 *
 * @ingroup openid_connect_api
 */
function hook_openid_connect_post_authorize(array $tokens, UserInterface $account, array $userinfo, $plugin_id) {
}

/**
 * Pre authorize hook that runs before a user is authorized.
 *
 * @param array $tokens
 *   An array of tokens.
 * @param \Drupal\user\UserInterface $account
 *   A user account object.
 * @param array $userinfo
 *   An array of user information.
 * @param string $plugin_id
 *   The plugin identifier.
 * @param string $sub
 *   The remote user identifier.
 */
function hook_openid_connect_pre_authorize(array $tokens, UserInterface $account, array $userinfo, $plugin_id, $sub) {
}

/**
 * Userinfo claim alter hook.
 *
 * This hook runs for every IdP provided userinfo claim mapped to a user
 * property, just before the OpenID Connect module maps its value to the
 * user property.
 *
 * A popular use for this hook is preprocessing claim values from a certain
 * IdP to match the property type of the target user property.
 *
 * @param mixed $claim_value
 *   The claim value.
 * @param array $context
 *   An context array containing:
 *   - claim:            The current claim.
 *   - property_name:    The property the claim is mapped to.
 *   - property_type:    The property type the claim is mapped to.
 *   - userinfo_mapping: The complete userinfo mapping.
 *   - tokens:           Array of original tokens.
 *   - user_data:        Array of user and session data from the ID token.
 *   - userinfo:         Array of user information from the userinfo endpoint.
 *   - plugin_id:        The plugin identifier.
 *   - sub:              The remote user identifier.
 *   - is_new:           Whether the account was created during authorization.
 */
function hook_openid_connect_userinfo_claim_alter(&$claim_value, array $context) {
  // Alter only, when the claim comes from the 'generic' identiy provider and
  // the property is 'telephone'.
  if (
    $context['plugin_id'] != 'generic'
    || $context['property_name'] != 'telephone'
  ) {
    return;
  }

  // Replace international number indicator with double zero.
  str_replace('+', '00', $claim_value);
}

/**
 * Save userinfo hook.
 *
 * This hook runs after the claim mappings have been applied by the OpenID
 * Connect module, but before the account will be saved.
 *
 * A popular use case for this hook is mapping additional information like
 * user roles or other complex claims provided by the identity provider, that
 * the OpenID Connect module has no mapping mechanisms for.
 *
 * @param \Drupal\user\UserInterface $account
 *   A user account object.
 * @param array $context
 *   An associative array with context information:
 *   - tokens:         Array of original tokens.
 *   - user_data:      Array of user and session data from the ID token.
 *   - userinfo:       Array of user information from the userinfo endpoint.
 *   - plugin_id:      The plugin identifier.
 *   - sub:            The remote user identifier.
 *   - is_new:         Whether the account was created during authorization.
 *
 * @ingroup openid_connect_api
 */
function hook_openid_connect_userinfo_save(UserInterface $account, array $context) {
  // Update only when the required information is available.
  if (
    $context['plugin_id'] != 'generic'
    || empty($context['userinfo']['my_info'])
  ) {
    return;
  }

  // Note: For brevity, this example does not validate field
  // types, nor does it implement error handling.
  $my_info = $context['userinfo']['my_info'];
  foreach ($my_info as $key => $value) {
    $account->set('field_' . $key, $value);
  }
}
