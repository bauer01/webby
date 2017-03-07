<?php

namespace Webby\Model\Entity;

use UniMapper\Entity;

/**
 * @adapter Webby
 *
 * @property string      $title
 * @property Section[]   $sections
 * @property Structure[] $structures
 */
class Layout extends Entity
{}