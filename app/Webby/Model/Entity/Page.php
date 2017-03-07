<?php

namespace Webby\Model\Entity;

use UniMapper\Entity;

/**
 * @adapter Webby(pages)
 *
 * @property string    $link     m:primary
 * @property string    $title
 * @property Section[] $sections
 */
class Page extends Entity
{}