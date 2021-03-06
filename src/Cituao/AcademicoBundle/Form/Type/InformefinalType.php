<?php
// src/Cituao/AcademicoBundle/Form/Type/InformefinalType.php
namespace Cituao\AcademicoBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class InformefinalType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
		        ->add('metodologia','textarea', array('required' => true, 'label' => ' ', 'max_length' => '5500', 'attr' => array('cols' => '130', 'rows' => '5', 'placeholder' => 'Escriba aqui...')))
		        ->add('competencia','textarea', array('required' => true, 'label' => ' ', 'max_length' => '5500' , 'attr' => array('cols' => '130', 'rows' => '10', 'placeholder' => 'Escriba aqui...')))
		        ->add('recomendaciones','textarea', array('required' => true, 'label' => ' ', 'max_length' => '5500', 'attr' => array('cols' => '130', 'rows' => '5', 'placeholder' => 'Escriba aqui...')));
		}

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Cituao\AcademicoBundle\Entity\Informefinalacademico', 'cascade_validation' => true
        ));
    }

    public function getName()
    {
        return 'informefinalaca';
    }
}
