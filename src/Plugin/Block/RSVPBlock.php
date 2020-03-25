<?php
/**
 * @file
 * Contains Drupal\rsvplist\Plugin\Block\RSVPBlock
 */

namespace Drupal\rsvplist\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Annotation\Translation;
use Drupal\Core\Block\Annotation\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Session\AccountInterface;

/**
 * Provides an RSVP list block
 * @Block(
 *   id = "rsvp_block",
 *   admin_label=@Translation("RSVP Block"),
 * )
 */
class RSVPBlock extends BlockBase
{
  /**
   * @inheritDoc
   */
  public function build()
  {
    return \Drupal::formBuilder()->getForm('Drupal\rsvplist\Form\RSVPForm');
  }

  /**
   * @inheritDoc
   */
  protected function blockAccess(AccountInterface $account)
  {
    /** @var Drupal\node\Entity\Node @node */
    $node = \Drupal::routeMatch()->getParameter('node');
    $nid = $node->nid->value;
    if (is_numeric($nid)) {
      return AccessResult::allowedIfHasPermissions($account, ['view rsvplist']);
    }
    return AccessResult::forbidden();
  }
}
