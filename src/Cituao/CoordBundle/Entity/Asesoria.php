<?php

namespace Cituao\CoordBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Asesoria
 */
class Asesoria
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $practicante;

    /**
     * @var integer
     */
    private $academico;

    /**
     * @var string
     */
    private $docAsesor1;

    /**
     * @var string
     */
    private $docPracticante1;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set practicante
     *
     * @param integer $practicante
     * @return Asesoria
     */
    public function setPracticante($practicante)
    {
        $this->practicante = $practicante;
    
        return $this;
    }

    /**
     * Get practicante
     *
     * @return integer 
     */
    public function getPracticante()
    {
        return $this->practicante;
    }

    /**
     * Set academico
     *
     * @param integer $academico
     * @return Asesoria
     */
    public function setAcademico($academico)
    {
        $this->academico = $academico;
    
        return $this;
    }

    /**
     * Get academico
     *
     * @return integer 
     */
    public function getAcademico()
    {
        return $this->academico;
    }

    /**
     * Set docAsesor1
     *
     * @param string $docAsesor1
     * @return Asesoria
     */
    public function setDocAsesor1($docAsesor1)
    {
        $this->docAsesor1 = $docAsesor1;
    
        return $this;
    }

    /**
     * Get docAsesor1
     *
     * @return string 
     */
    public function getDocAsesor1()
    {
        return $this->docAsesor1;
    }

    /**
     * Set docPracticante1
     *
     * @param string $docPracticante1
     * @return Asesoria
     */
    public function setDocPracticante1($docPracticante1)
    {
        $this->docPracticante1 = $docPracticante1;
    
        return $this;
    }

    /**
     * Get docPracticante1
     *
     * @return string 
     */
    public function getDocPracticante1()
    {
        return $this->docPracticante1;
    }
}