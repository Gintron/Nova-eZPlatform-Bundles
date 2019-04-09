<?php
/**
 * MC-convergence-ezp.
 *
 * @package   MC-convergence-ezp
 *
 * @author    Novactive <f.alexandre@novactive.com>
 * @copyright 2018 Novactive
 */

namespace Novactive\EzMenuManagerBundle\Entity;

class MenuSearch
{
    /**
     * @var string|null
     */
    protected $name;

    /**
     * @var string|null
     */
    protected $type;

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     */
    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @param string|null $type
     */
    public function setType(?string $type): void
    {
        $this->type = $type;
    }
}
