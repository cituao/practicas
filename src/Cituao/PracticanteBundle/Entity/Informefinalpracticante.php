<?php

namespace Cituao\PracticanteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

 /**
 * @ORM\Entity
 * @ORM\Table(name="Informefinalpracticante")
 *
 */
class Informefinalpracticante
{
	 /**
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $id;

     /**
     * @ORM\Column(type="integer")
	 */
    private $practicante;

     /**
     * @ORM\Column(type="text")
	 */
    private $comunicacion;

     /**
     * @ORM\Column(type="text")
	 */
    private $asesor;

     /**
     * @ORM\Column(type="text")
	 */
    private $coordinacion;

     /**
     * @ORM\Column(type="text")
	 */
    private $universidad;

     /**
     * @ORM\Column(type="text")
	 */
    private $autoreflexion;

     /**
     * @ORM\Column(type="text")
	 */
    private $recomendaciones;


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
     * @return Informefinalpracticante
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
     * Set comunicacion
     *
     * @param string $comunicacion
     * @return Informefinalpracticante
     */
    public function setComunicacion($comunicacion)
    {
        $this->comunicacion = $comunicacion;
    
        return $this;
    }

    /**
     * Get comunicacion
     *
     * @return string 
     */
    public function getComunicacion()
    {
        return $this->comunicacion;
    }

    /**
     * Set asesor
     *
     * @param string $asesor
     * @return Informefinalpracticante
     */
    public function setAsesor($asesor)
    {
        $this->asesor = $asesor;
    
        return $this;
    }

    /**
     * Get asesor
     *
     * @return string 
     */
    public function getAsesor()
    {
        return $this->asesor;
    }

    /**
     * Set coordinacion
     *
     * @param string $coordinacion
     * @return Informefinalpracticante
     */
    public function setCoordinacion($coordinacion)
    {
        $this->coordinacion = $coordinacion;
    
        return $this;
    }

    /**
     * Get coordinacion
     *
     * @return string 
     */
    public function getCoordinacion()
    {
        return $this->coordinacion;
    }

    /**
     * Set universidad
     *
     * @param string $universidad
     * @return Informefinalpracticante
     */
    public function setUniversidad($universidad)
    {
        $this->universidad = $universidad;
    
        return $this;
    }

    /**
     * Get universidad
     *
     * @return string 
     */
    public function getUniversidad()
    {
        return $this->universidad;
    }

    /**
     * Set autoreflexion
     *
     * @param string $autoreflexion
     * @return Informefinalpracticante
     */
    public function setAutoreflexion($autoreflexion)
    {
        $this->autoreflexion = $autoreflexion;
    
        return $this;
    }

    /**
     * Get autoreflexion
     *
     * @return string 
     */
    public function getAutoreflexion()
    {
        return $this->autoreflexion;
    }

    /**
     * Set recomendaciones
     *
     * @param string $recomendaciones
     * @return Informefinalpracticante
     */
    public function setRecomendaciones($recomendaciones)
    {
        $this->recomendaciones = $recomendaciones;
    
        return $this;
    }

    /**
     * Get recomendaciones
     *
     * @return string 
     */
    public function getRecomendaciones()
    {
        return $this->recomendaciones;
    }
}