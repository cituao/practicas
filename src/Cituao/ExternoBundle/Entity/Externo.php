<?php

namespace Cituao\ExternoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Cituao\ExternoBundle\Entity\Externo
 *
 * @ORM\Table(name="Externo")
 * @ORM\Entity(repositoryClass="Cituao\ExternoBundle\Entity\ExternoRepository")
 */
class Externo
{

	 /**
	 * @ORM\Column(type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
    private $id;

     /**
     * @ORM\Column(type="string", length=50)
	 * @Assert\NotBlank(message="Es obligatorio!")
	 * @Assert\Regex(pattern="/\d/", match=false, message="Nombre inválido!")
	 */
    private $nombres;

     /**
     * @ORM\Column(type="string", length=50)
     * @Assert\NotBlank(message="Es obligatorio!")
	 * @Assert\Regex(pattern="/\d/", match=false, message="Apellido inválido!")
	 */
    private $apellidos;

     /**
     * @ORM\Column(type="string", length=12, unique=true)
     * @Assert\NotBlank(message="Es obligatorio!")
     * @Assert\Regex(pattern="/^\d+$/", match=true, message="Cédula inválida!")
	 */
    private $ci;

    /**
    * @ORM\Column(type="string", length=15)
    */
    private $telefonoMovil;

     /**
     * @ORM\Column(type="string", length=15)
     */
    private $telefonoFijo;

     /**
     * @ORM\Column(type="string", length=50)
	 * @Assert\Email(message="Email inválido!")
     */
    private $email;

     /**
     * @ORM\Column(type="string", length=30)
     */
    private $cargo;

    

    /**
	* @ORM\OneToMany(targetEntity="Cituao\CoordBundle\Practicante", mappedBy = "externo")	
	**/
	protected $practicantes;

	protected $programas;

	protected $centros;
	
	private $activos;
	
	public function __construct()
    {
        $this->practicantes = new ArrayCollection();
		$this->programas = new ArrayCollection();
		$this->centros = new ArrayCollection();
    }
	
	
	public function __toString(){

		return sprintf('%s %s',$this->nombres, $this->apellidos);

	}
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
     * Set nombres
     *
     * @param string $nombres
     * @return Externo
     */
    public function setNombres($nombres)
    {
        $this->nombres = $nombres;
    
        return;
    }

    /**
     * Get nombres
     *
     * @return string 
     */
    public function getNombres()
    {
        return $this->nombres;
    }

    /**
     * Set apellidos
     *
     * @param string $apellidos
     * @return Externo
     */
    public function setApellidos($apellidos)
    {
        $this->apellidos = $apellidos;
    
        return;
    }

    /**
     * Get apellidos
     *
     * @return string 
     */
    public function getApellidos()
    {
        return $this->apellidos;
    }


    /**
     * Set ci
     *
     * @param string $ci
     * @return Externo
     */
    public function setCi($ci)
    {
        $this->ci = $ci;
    
        return;
    }

    /**
     * Get ci
     *
     * @return string 
     */
    public function getCi()
    {
        return $this->ci;
    }

    /**
     * Set telefonoMovil
     *
     * @param string $telefonoMovil
     * @return Externo
     */
    public function setTelefonoMovil($telefonoMovil)
    {
        $this->telefonoMovil = $telefonoMovil;
    
        return;
    }

    /**
     * Get telefonoMovil
     *
     * @return string 
     */
    public function getTelefonoMovil()
    {
        return $this->telefonoMovil;
    }

    /**
     * Set telefonoFijo
     *
     * @param string $telefonoFijo
     * @return Externo
     */
    public function setTelefonoFijo($telefonoFijo)
    {
        $this->telefonoFijo = $telefonoFijo;
    
        return;
    }

    /**
     * Get telefonoFijo
     *
     * @return string 
     */
    public function getTelefonoFijo()
    {
        return $this->telefonoFijo;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return Externo
     */
    public function setEmail($email)
    {
        $this->email = $email;
    
        return;
    }

    /**
     * Get email
     *
     * @return string 
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set cargo
     *
     * @param string $cargo
     * @return Externo
     */
    public function setCargo($cargo)
    {
        $this->cargo = $cargo;
    
        return;
    }

    /**
     * Get cargo
     *
     * @return string 
     */
    public function getCargo()
    {
        return $this->cargo;
    }
	
		/**
	 *
	 * nombre completo para los select 
	 */
	public function getNombreCompleto(){

		return sprintf('%s %s',$this->nombres, $this->apellidos);
	}


    /**
     * Add practicantes
     *
     * @param \Cituao\CoordBundle\Entity\Practicante $practicantes
     * @return Externo
     */
    public function addPracticante(\Cituao\CoordBundle\Entity\Practicante $practicantes)
    {
        $this->practicantes[] = $practicantes;
    
        return $this;
    }

    /**
     * Remove practicantes
     *
     * @param \Cituao\CoordBundle\Entity\Practicante $practicantes
     */
    public function removePracticante(\Cituao\CoordBundle\Entity\Practicante $practicantes)
    {
        $this->practicantes->removeElement($practicantes);
    }

    /**
     * Get practicantes
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getPracticantes()
    {
        return $this->practicantes;
    }

    /**
     * Add programas
     *
     * @param \Cituao\UsuarioBundle\Entity\Programa $programas
     * @return Externo
     */
    public function addPrograma(\Cituao\UsuarioBundle\Entity\Programa $programa)
    {
        $this->programas[] = $programa;
    
        return $this;
    }

    /**
     * Remove programas
     *
     * @param \Cituao\UsuarioBundle\Entity\Programa $programas
     */
    public function removePrograma(\Cituao\UsuarioBundle\Entity\Programa $programa)
    {
        $this->programas->removeElement($programa);
    }

    /**
     * Get programas
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getProgramas()
    {
        return $this->programas;
    }
	
	public function getActivos(){
		//calculamos los practicante activos del asesor 
		$culminado=0;
		$total=0;
		$listaPracticantes= $this->getPracticantes();
		foreach($listaPracticantes as $practicante){
			if ($practicante->getEstado() == 2) $culminado = $culminado+1;
			$total = $total + 1;
		}
		
		$this->activos=$listaPracticantes->count()-$culminado;
		
		return $this->activos;
	}

    /**
     * Add centros
     *
     * @param \Cituao\CoordBundle\Entity\Centro $centros
     * @return Externo
     */
    public function addCentro(\Cituao\CoordBundle\Entity\Centro $centro)
    {
        $this->centros[] = $centro;
    
        return $this;
    }

    /**
     * Remove centros
     *
     * @param \Cituao\CoordBundle\Entity\Centro $centro
     */
    public function removeCentro(\Cituao\CoordBundle\Entity\Centro $centro)
    {
        $this->programas->removeElement($centro);
    }

    /**
     * Get centros
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getCentros()
    {
        return $this->centros;
    }

}
