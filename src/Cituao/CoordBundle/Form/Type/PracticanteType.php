<?php
// src/Cituao/CoordBundle/Form/Type/PracticanteType.php
namespace Cituao\CoordBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PracticanteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
		->add('file')
		->add('ci','text', array('label' => 'Cédula de identidad' ,  'attr' => array('placeholder' => 'Ingrese cédula de identidad')))	    
		->add('codigo','text', array('label' => 'Código' ,  'attr' => array('placeholder' => 'Ingrese código del estudiante')))
        ->add('apellidos','text', array('label' => 'Apellidos' ,  'attr' => array('placeholder' => 'Ingrese apellidos')))
		->add('nombres','text', array('label' => 'Nombres' ,  'attr' => array('placeholder' => 'Ingrese nombres del partipante')))
        ->add('emailInstitucional', 'email',  array('label' => 'Email institucional' ,  'attr' => array('placeholder' => 'usuario@servidor')))
        ->add('emailPersonal', 'email',  array('label' => 'Email personal',  'attr' => array('placeholder' => 'usuario@servidor')))
		->add('telefonoMovil','text', array('label' => 'Teléfono móvil' ,  'attr' => array('placeholder' => 'Ingrese el número telefónico móvil ')))
		->add('fechaMatriculacion', 'date', array('label' => 'Fecha de matrícula', 'input' => 'datetime', 'widget' => 'choice', 'empty_value' => array('year' => 'Año', 'month' => 'Mes', 'day' => 'Día') ))
		->add('area','entity', array('class' => 'CituaoCoordBundle:Area' , 'property'=>'area'));

		//->add('ci','text', array('label' => 'Cédula de identidad','read_only'=>'true'))    
		}

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Cituao\CoordBundle\Entity\Practicante'
        ));
    }

    public function getName()
    {
        return 'practicante';
    }
}
