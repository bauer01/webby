<?php

namespace Webby\Model\Entity;

use UniMapper\Entity;

/**
 * @adapter Webby(themes)
 *
 * @property string   $name     m:primary
 * @property string   $layout
 * @property Layout[] $layouts
 */
class Theme extends Entity
{}