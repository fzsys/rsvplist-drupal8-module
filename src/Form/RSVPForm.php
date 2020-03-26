<?php
/**
 * @file
 * Contains \Drupal\rsvplist\Form\RSVPForm
 */
namespace Drupal\rsvplist\Form;

use Drupal\Console\Bootstrap\Drupal;
use Drupal\Core\Database\Database;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\user\Entity\User;

/**
 * Provides ar RSVP Email form
 * @package Drupal\rsvplist\Form
 */
class RSVPForm extends FormBase
{
  /**
   * @inheritDoc
   */
  public function getFormId()
  {
    return'rsvplist_email_form';
  }

  /**
   * @inheritDoc
   */
  public function buildForm(array $form, FormStateInterface $form_state)
  {
    $node = \Drupal::routeMatch()->getParameter('node');
    $nid = $node->nid->value;
    $form['email'] = [
      '#title' => t('Email Address'),
      '#type' => 'textfield',
      '#size' => 25,
      '#description' => t('We\'ll send updates to the email address you provide'),
      '#required' => TRUE,
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => t('RSVP'),
    ];
    $form['nid'] = [
      '#type' => 'hidden',
      '#value' => $nid,
    ];
    return $form;
  }

  /**
   * @inheritDoc
   */
  public function validateForm(array &$form, FormStateInterface $form_state)
  {
    $value = $form_state->getValue('email');

    if ($value == !\Drupal::service('email.validator')->isValid($value)) {
      $form_state->setErrorByName('email', t('Email %mail is not valid', ['%mail' => $value]));
    }

    $node = \Drupal::routeMatch()->getParameter('node');
    $select = Database::getConnection()->select('rsvplist', 'r');
    $select->fields('r', ['nid']);
    $select->condition('nid', $node->id());
    $select->condition('mail', $value);
    $result = $select->execute();

    if (!empty($result->fetchCol())) {
      $form_state->setErrorByName('email', t('The address %mail is already subscribed to this list', ['%mail' => $value]));
    }
  }

  /**
   * @inheritDoc
   */
  public function submitForm(array &$form, FormStateInterface $form_state)
  {
    $user = User::load(\Drupal::currentUser()->id());
    \Drupal::database()->insert('rsvplist')
      ->fields([
        'mail' => $form_state->getValue('email'),
        'nid' => $form_state->getValue('nid'),
        'uid' => $user->id(),
        'created' => time(),
      ])
      ->execute();
    \Drupal::messenger()->addMessage(t('Thank you for your RSVP to this event'));
  }
}
