<?php

namespace Cituao\CoordBundle\Controller;

use Cituao\CoordBundle\Entity\Document;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\HttpFoundation\Response;
use Cituao\CoordBundle\Entity\Practicante;
use Cituao\UsuarioBundle\Entity\Usuario;
use Cituao\UsuarioBundle\Entity\Role;
use Cituao\UsuarioBundle\Entity\Periodo;
use Cituao\ExternoBundle\Entity\Externo;
use Cituao\CoordBundle\Form\Type\PracticanteType;
use Cituao\CoordBundle\Form\Type\CoordinadorType;
use Cituao\CoordBundle\Form\Type\ExternoType;
use Cituao\AcademicoBundle\Entity\Academico;
use Cituao\AcademicoBundle\Entity\Cronograma;
use Cituao\ExternoBundle\Entity\Cronogramaexterno;
use Cituao\CoordBundle\Form\Type\AcademicoType;
use Cituao\CoordBundle\Entity\Area;
use Cituao\CoordBundle\Entity\Centro;
use Cituao\CoordBundle\Form\Type\CentroType;
use Cituao\CoordBundle\Form\Type\CronogramaType;
use \DateTime;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;


class DefaultController extends Controller
{
	/********************************************************/
	//home del coordinador
	/********************************************************/	
	public function indexAction()
	{
		$document = new Document();
		$form = $this->createFormBuilder($document)
		->add('file')
		->add('name')
		->getForm();

		//buscamos el programa
		$user = $this->get('security.context')->getToken()->getUser();
		$coordinador =  $user->getUsername();
		$repository = $this->getDoctrine()->getRepository('CituaoUsuarioBundle:Programa');
		$programa = $repository->findOneByCoordinador($coordinador);

		//buscamos los periodos y el periodo actual
		$repository = $this->getDoctrine()->getRepository('CituaoUsuarioBundle:Periodo');
		$query = $repository->createQueryBuilder('p')
				->orderBy('p.id','DESC')
				->getQuery();
		$periodos = $query->getResult();
		foreach ($periodos as $periodoActual){
			$dataperiodo = array('id' => $periodoActual->getId(), 'nombre' => $periodoActual->getNombre());
			break;
		}
		
		//obtenemos los practicantes
		$repository = $this->getDoctrine()->getRepository('CituaoCoordBundle:Practicante');
		$query = $repository->createQueryBuilder('p')
				->where('p.programa = :id_programa')
				->andWhere('p.periodo = :id_periodo')
				->andWhere('p.estado = 1')
				->setParameter('id_programa', $programa->getId())
				->setParameter('id_periodo', $periodoActual->getId())
				->orderBy('p.apellidos', 'ASC')
				->getQuery();
				
		//->setParameter('id_programa', $programa->getId())
				$listaPracticantes = $query->getResult();

		if ($listaPracticantes == NULL) {
			$msgerr = array('descripcion'=>'No hay practicantes registrados!','id'=>'1');
		}else{
			$msgerr = array('descripcion'=>'','id'=>'0');
		}
		
		$filtro = array('periodo' => $periodoActual->getId(), 'estado' => '1');
		return $this->render('CituaoCoordBundle:Default:practicantes.html.twig', array('filtro' => $filtro, 'periodos' => $periodos, 'form' => $form->createView() , 'listaPracticantes' => $listaPracticantes, 'programa' => $programa, 'msgerr' => $msgerr, 'dataperiodo' => $dataperiodo));
	}
	
	/********************************************************/
	//Listar los practicantes registrados en la base de datos
	/********************************************************/	
	public function practicantesAction(){
		$document = new Document();
		$form = $this->createFormBuilder($document)
		->add('file')
		->add('name')
		->getForm();

		//buscamos el programa
		$user = $this->get('security.context')->getToken()->getUser();
		$coordinador =  $user->getUsername();
		$repository = $this->getDoctrine()->getRepository('CituaoUsuarioBundle:Programa');
		$programa = $repository->findOneByCoordinador($coordinador);

		//buscamos los periodos y el periodo actual
		$repository = $this->getDoctrine()->getRepository('CituaoUsuarioBundle:Periodo');
		$query = $repository->createQueryBuilder('p')
				->orderBy('p.id','DESC')
				->getQuery();
		$periodos = $query->getResult();
		foreach ($periodos as $periodoActual){
			$dataperiodo = array('id' => $periodoActual->getId(), 'nombre' => $periodoActual->getNombre());
			break;
		}
		
		//obtenemos los practicantes
		$repository = $this->getDoctrine()->getRepository('CituaoCoordBundle:Practicante');
		$query = $repository->createQueryBuilder('p')
				->where('p.programa = :id_programa')
				->andWhere('p.periodo = :id_periodo')
				->andWhere('p.estado = 1')
				->setParameter('id_programa', $programa->getId())
				->setParameter('id_periodo', $periodoActual->getId())
				->orderBy('p.apellidos', 'ASC')
				->getQuery();
				
		//->setParameter('id_programa', $programa->getId())
				$listaPracticantes = $query->getResult();

		if ($listaPracticantes == NULL) {
			$msgerr = array('descripcion'=>'No hay practicantes registrados!','id'=>'0');
		}else{
			$msgerr = array('descripcion'=>'','id'=>'0');
		}
		
		$filtro = array('periodo' => $periodoActual->getId(), 'estado' => '1');
		return $this->render('CituaoCoordBundle:Default:practicantes.html.twig', array('filtro' => $filtro, 'periodos' => $periodos, 'form' => $form->createView() , 'listaPracticantes' => $listaPracticantes, 'programa' => $programa, 'msgerr' => $msgerr, 'dataperiodo' => $dataperiodo));
	}

	/***************************************************************************/
	//Muestra formulario para registrar un nuevo practicante en la base de datos
	/***************************************************************************/		
	public function registrarPracticanteAction(){
		$peticion = $this->getRequest();
		$em = $this->getDoctrine()->getManager();
		$practicante = new Practicante();
		$formulario = $this->createForm(new PracticanteType(), $practicante);
		$formulario->handleRequest($peticion);

				//buscamos el programa
		$user = $this->get('security.context')->getToken()->getUser();
		$coordinador =  $user->getUsername();
		$repository = $this->getDoctrine()->getRepository('CituaoUsuarioBundle:Programa');
		$programa = $repository->findOneByCoordinador($coordinador);
		
		//validamos que no existe la cédula y el código
		$repository = $this->getDoctrine()->getRepository('CituaoCoordBundle:Practicante');
		$p = $repository->findOneBy(array('codigo' => $practicante->getCodigo()));

		if ($p != NULL){
			throw $this->createNotFoundException('¡El código ingresado ya existe!');
		}else{
			$p = $repository->findOneBy(array('ci' => $practicante->getCi()));
			if ($p != NULL)  throw $this->createNotFoundException('¡La cédula de identidad ya existe!');
		}
		
		if ($formulario->isValid()) {
			//buscamos el programa
			$user = $this->get('security.context')->getToken()->getUser();
			$coordinador =  $user->getUsername();
			$repository = $this->getDoctrine()->getRepository('CituaoUsuarioBundle:Programa');
			$programa = $repository->findOneByCoordinador($coordinador);

			//buscamos los periodos y el periodo actual
			$repository = $this->getDoctrine()->getRepository('CituaoUsuarioBundle:Periodo');
			$query = $repository->createQueryBuilder('p')
					->orderBy('p.id','DESC')
					->getQuery();
			$periodos = $query->getResult();
			foreach ($periodos as $periodoActual){
				break;
			}

			//si subio no subio foto  le asignamos una foto generica
			if ($practicante->getFile() == NULL) $practicante->setPath('defaultPicture.png');
			//subimos la foto al servidor
			$practicante->upload();
			$practicante->setEstado("0");  //es practicante sin cronograma
			$practicante->setPrograma($programa); //le asignamos el programa
			$practicante->setPeriodo($periodoActual); //le asignamos el periodo actual

			// Completar las propiedades que el usuario no rellena en el formulario
			$em->persist($practicante);


			//los roles fueron cargados de forma manual en la base de datos
			//buscamos una instancia role tipo practicante 
			$codigo = 2; //2 corresponde a practicante		
			$repository = $this->getDoctrine()->getRepository('CituaoUsuarioBundle:Role');
			$role = $repository->findOneBy(array('id' => $codigo));

			$usuario = new Usuario();
			//cargamos todos los atributos al usuario
			$usuario->setUsername($practicante->getCodigo());
			$usuario->setPassword($practicante->getCi());
			$usuario->setSalt(md5(time()));
			$usuario->addRole($role); //cargamos el rol al coordinador
			$usuario->setIsActive(false); //no puede tener acceso 

			//codificamos el password			
			$encoder = $this->get('security.encoder_factory')->getEncoder($usuario);
			$passwordCodificado = $encoder->encodePassword($usuario->getPassword(), $usuario->getSalt());
			$usuario->setPassword($passwordCodificado);
			//guardamos usuario
			$em->persist($usuario);
			$em->flush();

            // Crear un mensaje flash para notificar al usuario
			$this->get('session')->getFlashBag()->add('info',
				'¡Listo practicante registrado correctamente!'
				);
			return $this->redirect($this->generateUrl('cituao_coord_practicantes'));
		}
		return $this->render('CituaoCoordBundle:Default:registrarpracticante.html.twig', array('formulario' => $formulario->createView(), 'programa' => $programa ));
	}


	/**********************************************************************/
	//Muestra y modifica un practicante registrado en la base de datos
	/**********************************************************************/		
	public function practicanteAction($codigo){
		$peticion = $this->getRequest();
		$em = $this->getDoctrine()->getManager();
		
		$repository = $this->getDoctrine()->getRepository('CituaoCoordBundle:Practicante');
		$practicante = $repository->findOneBy(array('codigo' => $codigo));
		
		//buscamos registro de usuario para actualizar username y password
		$repository = $this->getDoctrine()->getRepository('CituaoUsuarioBundle:Usuario');
		$usuario = $repository->findOneBy(array('username' => $codigo));
		
		$formulario = $this->createForm(new PracticanteType(), $practicante);
		
		$area = $practicante->getArea();
		if ($area->getId()==3)
		$formulario->get('area')->setData($area);
		
		$formulario->handleRequest($peticion);

		
		if ($formulario->isValid()) {
			$practicante->upload();				
            // Completar las propiedades que el usuario no rellena en el formulario
			$usuario->setUsername($practicante->getCodigo());
			$usuario->setPassword($practicante->getCi());
			$usuario->setSalt(md5(time()));
			//codificamos el password			
			$encoder = $this->get('security.encoder_factory')->getEncoder($usuario);
			$passwordCodificado = $encoder->encodePassword($usuario->getPassword(), $usuario->getSalt());
			$usuario->setPassword($passwordCodificado);
			
			//$usuario->setIsActive(false);

			$em->persist($practicante);
			$em->persist($usuario);
			$em->flush();

			// Crear un mensaje flash para notificar al usuario
			$this->get('session')->getFlashBag()->add('info',
				'¡Listo practicante modificado!'
				);
			return $this->redirect($this->generateUrl('cituao_coord_practicantes'));
		}
		
		//buscamos el programa
		$user = $this->get('security.context')->getToken()->getUser();
		$coordinador =  $user->getUsername();
		$repository = $this->getDoctrine()->getRepository('CituaoUsuarioBundle:Programa');
		$programa = $repository->findOneByCoordinador($coordinador);
		
		return $this->render('CituaoCoordBundle:Default:practicante.html.twig', array('formulario' => $formulario->createView(), 'practicante' => $practicante, 'programa' => $programa ));
	}

	/********************************************************/
	//Muestra y guarda cronograma de actividades del practicante 
	/********************************************************/		
	public function cronogramaAction($codigo){
		$peticion = $this->getRequest();
		$em = $this->getDoctrine()->getManager();

		//prerequisitos para establecer un cronograma dede existir centros de prácticas registrados
		$user = $this->get('security.context')->getToken()->getUser();
		$coordinador =  $user->getUsername();

		$repository = $this->getDoctrine()->getRepository('CituaoUsuarioBundle:Programa');
		$programa = $repository->findOneByCoordinador($coordinador);

		$listaCentros = $programa->getCentros();
		if ($listaCentros->count() == 0) {
			throw $this->createNotFoundException('ERR_NO_HAY_CENTROS');
		}

		//prerequisitos para establecer un cronograma debe existir asesores externos
		$listaAsesores = $programa->getExternos();
		if ($listaAsesores->count() == 0) {
			throw $this->createNotFoundException('ERR_NO_HAY_EXTERNOS');
		}
		
		//prerequisitos para establecer un cronograma debe existir asesores académicos
		$listaAcademicos = $programa->getAcademicos();
		if ($listaAcademicos->count() == 0) {
			throw $this->createNotFoundException('ERR_NO_HAY_ACADEMICOS');
		}
		
		//base de datos
		$repository = $this->getDoctrine()->getRepository('CituaoCoordBundle:Practicante');
		$practicante = $repository->findOneBy(array('codigo' => $codigo));

        //creamos instacia formulario para el conograma
		$formulario = $this->createForm(new CronogramaType($programa, $practicante), $practicante);
		$formulario->handleRequest($peticion);

		// si los datos son validos guardamos cronograma para los actores        
		if ($formulario->isValid()) {
			$p=10;
			if ($p==10){
			
		
			if ($practicante->getEstado() == 1) {
				//ya tiene cronograma estado = 1

				//verificamos que cambios hubo
				//en asesor academico
	
				$academico = $practicante->getAcademico(); 
			
				
				//cambio el asesor academico 
				//actuaizamos el id en el cronograma del asesor academico
				$query = $em->createQuery(
				'SELECT c FROM CituaoAcademicoBundle:Cronograma c WHERE c.practicante =:id_pra');
				$query->setParameter('id_pra',$practicante->getId());
				//como obtengo un solo object entonces necesito solo esa instancia no una array de instancias 			
				$cronograma_academico = $query->getOneOrNullResult();//getSingleResult();
				$cronograma_academico->setAcademico($academico->getId()); 
				$cronograma_academico->setFechaAsesoria1($practicante->getFechaAsesoria1());
				$cronograma_academico->setFechaAsesoria2($practicante->getFechaAsesoria2());
				$cronograma_academico->setFechaAsesoria3($practicante->getFechaAsesoria3());
				$cronograma_academico->setFechaAsesoria4($practicante->getFechaAsesoria4());
				$cronograma_academico->setFechaAsesoria5($practicante->getFechaAsesoria5());
				$cronograma_academico->setFechaAsesoria6($practicante->getFechaAsesoria6());
				$cronograma_academico->setFechaAsesoria7($practicante->getFechaAsesoria7());
				$cronograma_academico->setFechaVisitaP($practicante->getFechaVisitaP());
				$cronograma_academico->setFechaEvaluacion1($practicante->getFechaVisita1());
				$cronograma_academico->setFechaEvaluacion2($practicante->getFechaVisita2());
				$cronograma_academico->setFechaInformeGestion1($practicante->getFechaInformeGestion1());
				$cronograma_academico->setFechaInformeGestion2($practicante->getFechaInformeGestion2());
				$cronograma_academico->setFechaInformeGestion3($practicante->getFechaInformeGestion3());
				$cronograma_academico->setFechaEvaluacionFinal($practicante->getFechaInformeFinal());	
				$em->persist($cronograma_academico);
				
				$externo = $practicante->getExterno();
				//cambio el asesor externo 
				//actuaizamos el id en el cronograma del asesor academico
				$query = $em->createQuery(
				'SELECT c FROM CituaoExternoBundle:Cronogramaexterno c WHERE c.practicante =:id_pra');
				$query->setParameter('id_pra',$practicante->getId());
				//como obtengo un solo object entonces necesito solo esa instancia no una array de instancias 			
				$cronograma_externo = $query->getOneOrNullResult();//getSingleResult();
				$cronograma_externo->setExterno($externo->getId()); 
				$cronograma_externo->setFechaEvaluacion1($practicante->getFechaVisita1());
				$cronograma_externo->setFechaEvaluacion2($practicante->getFechaVisita2());
				$cronograma_externo->setFechaActa($practicante->getFechaInformeFinal());
				$em->persist($cronograma_externo);
			
				$em->persist($practicante);
				$em->flush();
				
				// Crear un mensaje flash para notificar al usuario que se ha registrado correctamente
				$this->get('session')->getFlashBag()->add('info',
					'¡Listo, cronograma modificado satisfactoriamente!'
					);
				return $this->redirect($this->generateUrl('cituao_coord_homepage'));
			}	
			
			else{
				$id_academico = $formulario->get('academico')->getData();
				
				$repository = $this->getDoctrine()->getRepository('CituaoAcademicoBundle:Academico');
				$academico = $repository->findOneById($id_academico);
				
				if ($academico->getActivos($programa)  == 30)	throw $this->createNotFoundException('ERR_MAX_PRACTICANTES');
				
				// Completar las propiedades que el usuario no rellena en el formulario
				$practicante->setEstado(true); //colocamos al practicante como activo ya que tiene calendario
			
				//creamos 
				$cronograma = new Cronograma();
				$cronograma->setPracticante($practicante->getId());
				$cronograma->setAcademico($practicante->getAcademico()->getId());
				//cargamos las fechas	
				$cronograma->setFechaAsesoria1($practicante->getFechaAsesoria1());
				$cronograma->setFechaAsesoria2($practicante->getFechaAsesoria2());
				$cronograma->setFechaAsesoria3($practicante->getFechaAsesoria3());
				$cronograma->setFechaAsesoria4($practicante->getFechaAsesoria4());
				$cronograma->setFechaAsesoria5($practicante->getFechaAsesoria5());
				$cronograma->setFechaAsesoria6($practicante->getFechaAsesoria6());
				$cronograma->setFechaAsesoria7($practicante->getFechaAsesoria7());
				$cronograma->setFechaVisitaP($practicante->getFechaVisitaP());
				$cronograma->setFechaEvaluacion1($practicante->getFechaVisita1());
				$cronograma->setFechaEvaluacion2($practicante->getFechaVisita2());
				$cronograma->setFechaInformeGestion1($practicante->getFechaInformeGestion1());
				$cronograma->setFechaInformeGestion2($practicante->getFechaInformeGestion2());
				$cronograma->setFechaInformeGestion3($practicante->getFechaInformeGestion3());
				$cronograma->setFechaEvaluacionFinal($practicante->getFechaInformeFinal());	
			
				$cronogramaexterno = new Cronogramaexterno();
				$cronogramaexterno->setPracticante($practicante->getId());
				$cronogramaexterno->setExterno($practicante->getExterno()->getId());
				//asignamos las fechas correspondientes al asesor externo
				$cronogramaexterno->setFechaEvaluacion1($practicante->getFechaVisita1());
				$cronogramaexterno->setFechaEvaluacion2($practicante->getFechaVisita2());
				$cronogramaexterno->setFechaActa($practicante->getFechaInformeFinal());

				//activamos el usuario del practicante
				$repository = $this->getDoctrine()->getRepository('CituaoUsuarioBundle:Usuario');
				$usuario = $repository->findOneBy(array('username' => $practicante->getCodigo()));
				$usuario->setIsActive(true);
				
				//activamos el usuario del externo
				$repository = $this->getDoctrine()->getRepository('CituaoUsuarioBundle:Usuario');
				$usuario_externo = $repository->findOneBy(array('username' => $practicante->getExterno()->getCi()));
				$usuario_externo->setIsActive(true);
				
				//activamos el usuario del academico
				$repository = $this->getDoctrine()->getRepository('CituaoUsuarioBundle:Usuario');
				$usuario_academico = $repository->findOneBy(array('username' => $practicante->getAcademico()->getCi()));
				$usuario_academico->setIsActive(true);
				
				$em->persist($cronogramaexterno);
				$em->persist($usuario);
				$em->persist($usuario_externo);
				$em->persist($usuario_academico);
				$em->persist($practicante);
				$em->persist($cronograma);
			
				$em->flush();
			
				// Crear un mensaje flash para notificar al usuario que se ha registrado correctamente
				$this->get('session')->getFlashBag()->add('info',
					'¡Listo, cronograma asignado satisfactoriamente!'
					);
			}		
				
			}	
				return $this->redirect($this->generateUrl('cituao_coord_homepage'));
			
		}
		
				//buscamos el programa
		$user = $this->get('security.context')->getToken()->getUser();
		$coordinador =  $user->getUsername();
		$repository = $this->getDoctrine()->getRepository('CituaoUsuarioBundle:Programa');
		$programa = $repository->findOneByCoordinador($coordinador);
		
		return $this->render('CituaoCoordBundle:Default:cronograma.html.twig', array('formulario' => $formulario->createView(), 'practicante' => $practicante , 'programa' => $programa));
	}


	/********************************************************/
	//SE ENCARGA DE LANZAR UN FORMULARIO PARA LA SUBIDA DEL ARCHIVO TXT CON ESTUDIANTES PARA IR A PRACTICAS PROFESIONALES
	/********************************************************/	
	public function uploadAction(Request $request)
	{
		$document = new Document();
		$form = $this->createFormBuilder($document)
		->add('file')
		->add('name')
		->getForm();

		$form->handleRequest($request);

		if ($form->isValid()) {
			//levantar servicios de doctrine base de datos
			$em = $this->getDoctrine()->getManager();

			//se copia el archivo al directorio del servidor			
			$document->upload();

			$em->persist($document);
		    //$em->flush();

			$archivo= $document->getAbsolutePath();		
			//bajamos el archivo a una matriz para procesar registro a registro y bajarlo a base de datos		    
			$filas = file($archivo);
			$i=0;
			$numero_fila= count($filas);	

			//para buscar si ya se encuentra en la base de datos
			$repository = $this->getDoctrine()->getRepository('CituaoCoordBundle:Practicante');

			$nohay = true;
			$numero_registrados=0;
			//procesamos la matriz separando los campos por medio del separador putno y coma
			while($i <= $numero_fila -1){
				$row = $filas[$i];
				$sql = explode(";",$row);

				$e = $repository->findOneBy(array('ci' => $sql[3]));
				//Si esta en la base de datos lo ignoramos				
				if ($e != NULL){
					$numero_registrados++;
					$i++;						
					continue;
				}

				$listaEstudiantes[$i] =  array("codigo"=> $sql[0], "apellidos"=>$sql[1], "nombres"=>$sql[2], "ci" => $sql[3], 	
					"fecha" => $sql[4], "emailInstitucional" => $sql[5] );
				$i++;
				$nohay = false;
			}
				//buscamos el programa
			$user = $this->get('security.context')->getToken()->getUser();
			$coordinador =  $user->getUsername();
			$repository = $this->getDoctrine()->getRepository('CituaoUsuarioBundle:Programa');
			$programa = $repository->findOneByCoordinador($coordinador);
		

			if (!$nohay){
				
				//los roles fueron cargados de forma manual en la base de datos
				//buscamos una instancia role tipo practicante 
				$codigo = 2; //1 corresponde a practicantes		
				$repository = $this->getDoctrine()->getRepository('CituaoUsuarioBundle:Role');
				$role = $repository->findOneBy(array('id' => $codigo));

					//buscamos el programa
				$user = $this->get('security.context')->getToken()->getUser();
				$coordinador =  $user->getUsername();
				$repository = $this->getDoctrine()->getRepository('CituaoUsuarioBundle:Programa');
				$programa = $repository->findOneByCoordinador($coordinador);
			
				//buscamos los periodos y el periodo actual
				$repository = $this->getDoctrine()->getRepository('CituaoUsuarioBundle:Periodo');
				$query = $repository->createQueryBuilder('p')
						->orderBy('p.id','DESC')
						->getQuery();
				$periodos = $query->getResult();
				foreach ($periodos as $periodoActual){
					break;
				}

				
				//procesamos la matriz  fila a fila creando practicantes y usuarios
				$i=0;				
				$sad = "";	
				
				while($i <= $numero_fila - $numero_registrados){
					//creamos una instancia Practicante para descargar datos del CSV y guardar en la base de datos
					$practicante = new Practicante();
					//creamos una instancia de usuario para darle entrada a los practicantes como usuarios en el sistema
					$usuario = new Usuario();

					//viene del archivo .csv	
					//cargamos todos los atributos al practicante
					$practicante->setCodigo($listaEstudiantes[$i]['codigo']);
					$practicante->setNombres($listaEstudiantes[$i]['nombres']);
					$practicante->setApellidos($listaEstudiantes[$i]['apellidos']);
					$practicante->setEmailInstitucional($listaEstudiantes[$i]['emailInstitucional']);
					$practicante->setCi($listaEstudiantes[$i]['ci']);
					$practicante->setPrograma($programa);
					$practicante->setPeriodo($periodoActual);
			
					
					//cargamos todos los atributos al usuario
					$usuario->setUsername($listaEstudiantes[$i]['codigo']) ;
					$usuario->setPassword($listaEstudiantes[$i]['ci']);
					$usuario->setSalt(md5(time()));
					$usuario->addRole($role); //cargamos el rol al coordinador
					$usuario->setIsActive(false); //no puede tener acceso 

					$encoder = $this->get('security.encoder_factory')->getEncoder($usuario);
		            $passwordCodificado = $encoder->encodePassword($usuario->getPassword(), $usuario->getSalt());
					$usuario->setPassword($passwordCodificado);


					 $em->persist($usuario);

					 //convertimos la fecha de matricula a un objeto Date				
					$fecha = $listaEstudiantes[$i]['fecha'];
					$separa = explode("/",$fecha);
					$dia = $separa[0];
					$mes = $separa[1];
					$ano = $separa[2];

					$f = new \DateTime();
					$f->setDate($ano,$mes,$dia);

					$practicante->setFechaMatriculacion($f);

					//cargamos los demas datos
					//$practicante->setTelefonoMovil($sad);

					$practicante->setEstado(false);

					$practicante->setPath('defaultPicture.png');
					$em->persist($practicante);
					$em->flush();
					$i++;
				}
				
				$total_registrados = $numero_fila - $numero_registrados;
				$descrip = 'Se registraron '.$total_registrados.' practicantes!';
				$msgerr = array('id'=>'0', 'descripcion'=> $descrip);
			} else {
				$msgerr = array('id'=>'1', 'descripcion'=>'No fue registrado ningún practicante ya existen en el sistema!');
				$listaEstudiantes = array();
			}
			
			
			
			return $this->render('CituaoCoordBundle:Default:practicantessubidos.html.twig', array('listaEstudiantes' => $listaEstudiantes, 'msgerr' => $msgerr , 'programa' => $programa));
		} 
		
		$msgerr = array('id'=>'0', 'descripcion'=>' ');
		
				//buscamos el programa
		$user = $this->get('security.context')->getToken()->getUser();
		$coordinador =  $user->getUsername();
		$repository = $this->getDoctrine()->getRepository('CituaoUsuarioBundle:Programa');
		$programa = $repository->findOneByCoordinador($coordinador);
		
		return $this->render('CituaoCoordBundle:Default:practicantes.html.twig', array('form' => $form->createView() , 'msgerr' => $msgerr , 'programa' => $programa));
	}

	/*************************************/
	//Listar todos los asesores externos		
	/*************************************/
	public function asesoresAction(){
		$user = $this->get('security.context')->getToken()->getUser();
		$coordinador =  $user->getUsername();

		$repository = $this->getDoctrine()->getRepository('CituaoUsuarioBundle:Programa');
		$programa = $repository->findOneByCoordinador($coordinador);

		$listaAsesores = $programa->getExternos();

		if ($listaAsesores->count() == 0) {
			$msgerr = array('descripcion'=>'No hay asesores externos registrados!','id'=>'1');
		}else{
			$msgerr = array('descripcion'=>'','id'=>'0');
		}
		
		//buscamos el programa
		return $this->render('CituaoCoordBundle:Default:externos.html.twig', array('listaAsesores' => $listaAsesores, 'msgerr' => $msgerr, 'programa' => $programa));
	} 


	/*********************************************/
	//Muestra y registra un asesor externo
	/*********************************************/	
	public function registrarexternoAction()
	{
		//verificamos los centros del programa
		$user = $this->get('security.context')->getToken()->getUser();
		$coordinador =  $user->getUsername();
		$repository = $this->getDoctrine()->getRepository('CituaoUsuarioBundle:Programa');
		$programa = $repository->findOneByCoordinador($coordinador);
		$centros = $programa->getCentros();
		
		//sino hay centros es una exception
		if (!$centros) {
			throw $this->createNotFoundException('Para crear un nuevo asesor externo debe haber centros de práctica registrados!');
		}

		//bajamos los datos de la peticion
		$peticion = $this->getRequest();
		$em = $this->getDoctrine()->getManager();
		$externo = new Externo();

		$formulario = $this->createForm(new ExternoType($programa->getId()), $externo);
		$formulario->handleRequest($peticion);

		if ($formulario->isValid()) {

			//buscamos si ya existe este asesor externo en la base de datos
			$repository = $this->getDoctrine()->getRepository('CituaoExternoBundle:Externo');
			$e = $repository->findOneBy(array('ci' => $externo->getCi()));

			if ($e != NULL){
				//el asesor ya existe determinamos si ya esta registrado en el programa del coordinador
				$sw=false;
				$programas_asesor_externo = $e->getProgramas();
				foreach ($programas_asesor_externo as $p) {
					if ($p->getId() == $programa->getId()) {
						$sw=true;
						break;
					}
				}
				if (!$sw){
					//lo agregamos al programa				
					$e->addPrograma($programa);

					//creamos un objeto con el centro selecionado por el coordinador				
					$idCentro = $formulario->get('centros')->getData();				
					$repository = $this->getDoctrine()->getRepository('CituaoCoordBundle:Centro');
					$centro = $repository->find($idCentro);
					$e->addCentro($centro);
					$em->persist($e);
			
					$em->flush();
					
					// Crear un mensaje flash para notificar al usuario
					$this->get('session')->getFlashBag()->add('info',
						'¡Listo se registro el asesor externo!'
					);
					return $this->redirect($this->generateUrl('cituao_coord_asesores'));
				}
				else{
					throw $this->createNotFoundException('ERR_EXTERNO_YA_EXISTE');
				}
			}
			else{
				//como no se encuentra registrado en el sistema 
				//validamos que la cedula no este ya registrada como otro usuariousername
				$repository = $this->getDoctrine()->getRepository('CituaoUsuarioBundle:Usuario');
				$u = $repository->findOneBy(array('username' => $externo->getCi()));
				if ($u != NULL){
					throw $this->createNotFoundException('ERR_USUARIO_YA_EXISTE');
				}

			   // Completar las propiedades que el usuario no rellena en el formulario
				
				//creamos un objeto con el centro selecionado por el coordinador				
				$idCentro = $formulario->get('centros')->getData();				
				$repository = $this->getDoctrine()->getRepository('CituaoCoordBundle:Centro');
				$centro = $repository->find($idCentro);
				$externo->addCentro($centro);

				//agregamos el programa			
			    $externo->addPrograma($programa);
				$em->persist($externo);

				//los roles fueron cargados de forma manual en la base de datos
				//buscamos una instancia role tipo coordinador 
				$codigo = 3; //3 codigo corresponde a coordinador		
				$repository = $this->getDoctrine()->getRepository('CituaoUsuarioBundle:Role');
				$role = $repository->findOneBy(array('id' => $codigo));

				if ($role == NULL){
					throw $this->createNotFoundException('ERR_ROLE_NO_ENCONTRADO');
				}
				$usuario = new Usuario();
				//cargamos todos los atributos al usuario
				$usuario->setUsername($externo->getCi());
				$usuario->setPassword($externo->getCi());
				$usuario->setSalt(md5(time()));
				$usuario->addRole($role);  //cargamos el rol al coordinador

				//codificamos el password			
				$encoder = $this->get('security.encoder_factory')->getEncoder($usuario);
				$passwordCodificado = $encoder->encodePassword($usuario->getPassword(), $usuario->getSalt());
				$usuario->setPassword($passwordCodificado);
				$usuario->setIsActive(false);
				$em->persist($usuario);

				// Crear un mensaje flash para notificar al usuario
				$this->get('session')->getFlashBag()->add('info',
					'¡Listo asesor externo registrado!'
				);

				$em->flush();
				return $this->redirect($this->generateUrl('cituao_coord_asesores'));
			}
		}
				//buscamos el programa
		$user = $this->get('security.context')->getToken()->getUser();
		$coordinador =  $user->getUsername();
		$repository = $this->getDoctrine()->getRepository('CituaoUsuarioBundle:Programa');
		$programa = $repository->findOneByCoordinador($coordinador);
		
		return $this->render('CituaoCoordBundle:Default:registrarexterno.html.twig', array('formulario' => $formulario->createView(), 'programa' => $programa));
	}


	/********************************************************/
	//Muestra y modifica un asesor externo registrado en la base de datos
	/********************************************************/		
	public function externoAction($ci){
		$peticion = $this->getRequest();
		$em = $this->getDoctrine()->getManager();

		$user = $this->get('security.context')->getToken()->getUser();
		$coordinador =  $user->getUsername();
		$repository = $this->getDoctrine()->getRepository('CituaoUsuarioBundle:Programa');
		$programa = $repository->findOneByCoordinador($coordinador);
		
		$lista_externos = $programa->getExternos();
		//buscamos una instancia cualquiera del asesor externo
		foreach($lista_externos as $externo){
			if ($externo->getCi() == $ci) break;
		}
		
		$formulario = $this->createForm(new ExternoType($programa), $externo);
		
		$lista_centros = $externo->getCentros();
		//buscamos el centro que corresponda con el programa en session
		foreach($lista_centros as $centro){
			if ($centro->getPrograma()->getId() == $programa->getId()) break;
		}
		//le cargamos el objeto centro al formulario
		$formulario->get('centros')->setData($centro);
		$formulario->handleRequest($peticion);
		if ($formulario->isValid()) {
			
            // Completar las propiedades que el usuario no rellena en el formulario
			$idCentro = $formulario->get('centros')->getData();
			
			
			$repository = $this->getDoctrine()->getRepository('CituaoCoordBundle:Centro');
			$centro = $repository->find($idCentro);
			$centros = $externo->getCentros();
			
			//buscamos si ya tiene el centro asignado
			$sw = false;
			foreach ($centros as $c){
				$id_current = $c->getId(); 
				if ($id_current == $idCentro->getId()) {
					$sw=true;
					break;
				}
			}			
			//si no tiene el centro asignado se lo asignamos
			if(!$sw){
				$externo->addCentro($centro);
			}
			

			$ci_form = $formulario->get('ci')->getData();
			//si el usuario cambio la cédula modificamos el username y password 
			if ($ci_form != $ci){
				$repository = $this->getDoctrine()->getRepository('CituaoUsuarioBundle:Usuario');
				$usuario = $repository->findOneBy(array('username' => $ci));
				
				$usuario->setUsername($ci_form);
				$usuario->setPassword($ci_form);
				$usuario->setSalt(md5(time()));

				//codificamos el password			
				$encoder = $this->get('security.encoder_factory')->getEncoder($usuario);
				$passwordCodificado = $encoder->encodePassword($usuario->getPassword(), $usuario->getSalt());
				$usuario->setPassword($passwordCodificado);
				$em->persist($usuario);
			}
			$em->persist($externo);
			$em->flush();

			// Crear un mensaje flash para notificar al usuario
			$this->get('session')->getFlashBag()->add('info',
				'¡Listo se modificó el asesor externo!'
			);
			return $this->redirect($this->generateUrl('cituao_coord_asesores'));
		}
		
				//buscamos el programa
		$user = $this->get('security.context')->getToken()->getUser();
		$coordinador =  $user->getUsername();
		$repository = $this->getDoctrine()->getRepository('CituaoUsuarioBundle:Programa');
		$programa = $repository->findOneByCoordinador($coordinador);
		
		return $this->render('CituaoCoordBundle:Default:externo.html.twig', array('formulario' => $formulario->createView(), 'externo' => $externo, 'programa' => $programa ));
		
	}

	/*************************************/
	//Listar todos los asesores ACADEMICOS		
	/*************************************/
	public function academicosAction()
	{
		$user = $this->get('security.context')->getToken()->getUser();
		$coordinador =  $user->getUsername();

		$repository = $this->getDoctrine()->getRepository('CituaoUsuarioBundle:Programa');
		$programa = $repository->findOneByCoordinador($coordinador);

		$listaAcademicos = $programa->getAcademicos();

		if ($listaAcademicos->count() == 0) {
			$msgerr = array('descripcion'=>'No hay asesores académicos registrados!','id'=>'1');
		}else{
			$msgerr = array('descripcion'=>'','id'=>'0');
			$c=0;	
			foreach ($listaAcademicos as $aca){
				$c=$aca->getActivos($programa);
			}
		}
		
		return $this->render('CituaoCoordBundle:Default:academicos.html.twig', array('listaAcademicos' => $listaAcademicos, 'msgerr' => $msgerr, 'programa' => $programa ));
	} 


	/*********************************************/
	//registra un asesor academico
	/*********************************************/	
	public function registraracademicoAction()
	{
		$peticion = $this->getRequest();
		$em = $this->getDoctrine()->getManager();
		$academico = new Academico();
		$formulario = $this->createForm(new AcademicoType(), $academico);
		$formulario->handleRequest($peticion);
		
		if ($formulario->isValid()) {
			// buscamos el programa del coordinador
			$user = $this->get('security.context')->getToken()->getUser();
			$coordinador =  $user->getUsername();
			$repository = $this->getDoctrine()->getRepository('CituaoUsuarioBundle:Programa');
			$programa = $repository->findOneByCoordinador($coordinador);


			//buscamos si ya existe este asesor academico en la base de datos
			$repository = $this->getDoctrine()->getRepository('CituaoAcademicoBundle:Academico');
			$a = $repository->findOneBy(array('ci' => $academico->getCi()));

			if ($a != NULL){
				//el asesor ya existe determinamos si ya esta registrado en el programa del coordinador
				$sw=false;
				$programas_asesor_academico = $a->getProgramas();
				foreach ($programas_asesor_academico as $p) {
					if ($p->getId() == $programa->getId()) {
						$sw=true;
						break;
					}
				}
				if (!$sw){
					//lo agregamos al programa				
					$a->addPrograma($programa);
					$em->flush();
					return $this->redirect($this->generateUrl('cituao_coord_academicos'));
				}
				else{
					throw $this->createNotFoundException('ERR_ACADEMICO_YA_EXISTE');
				}
			}
			else{
				//validamos que la cedula no este ya registrada como username
				$repository = $this->getDoctrine()->getRepository('CituaoUsuarioBundle:Usuario');
				$u = $repository->findOneBy(array('username' => $academico->getCi()));
			
				if ($u != NULL){
					throw $this->createNotFoundException('ERR_USUARIO_YA_EXISTE');
				}
				$academico->addPrograma($programa);
	
				if ($academico->getFile() == NULL) 	$academico->setPath('defaultPicture.png');
				$academico->upload();	
			
		        // Completar las propiedades que el usuario no rellena en el formulario
				$em->persist($academico);

				//los roles fueron cargados de forma manual en la base de datos
				//buscamos una instancia role tipo coordinador 
				$codigo = 4; //4 codigo corresponde a coordinador		
				$repository = $this->getDoctrine()->getRepository('CituaoUsuarioBundle:Role');
				$role = $repository->findOneBy(array('id' => $codigo));

				$usuario = new Usuario();
				//cargamos todos los atributos al usuario
				$usuario->setUsername($academico->getCi());
				$usuario->setPassword($academico->getCi());
				$usuario->setSalt(md5(time()));
				$usuario->addRole($role); //cargamos el rol al coordinador

				//codificamos el password			
				$encoder = $this->get('security.encoder_factory')->getEncoder($usuario);
				$passwordCodificado = $encoder->encodePassword($usuario->getPassword(), $usuario->getSalt());
				$usuario->setPassword($passwordCodificado);
				$usuario->setIsActive(false);
				$em->persist($usuario);

				$em->flush();

		        // Crear un mensaje flash para notificar al usuario que se ha registrado correctamente
				$this->get('session')->getFlashBag()->add('info',
					'¡Listo, se registro el asesor académico!'
					);
				return $this->redirect($this->generateUrl('cituao_coord_academicos'));
			}
		}

		//buscamos el programa
		$user = $this->get('security.context')->getToken()->getUser();
		$coordinador =  $user->getUsername();
		$repository = $this->getDoctrine()->getRepository('CituaoUsuarioBundle:Programa');
		$programa = $repository->findOneByCoordinador($coordinador);
		
		return $this->render('CituaoCoordBundle:Default:registraracademico.html.twig', array(
			'formulario' => $formulario->createView(), 'programa' => $programa
			));
	}

	/********************************************************/
	//Muestra y modifica un asesor academico registrado en la base de datos
	/********************************************************/		
	public function academicoAction($ci){
		$peticion = $this->getRequest();
		$em = $this->getDoctrine()->getManager();
		
		$repository = $this->getDoctrine()->getRepository('CituaoAcademicoBundle:Academico');
		$academico = $repository->findOneBy(array('ci' => $ci));
		
		$formulario = $this->createForm(new AcademicoType(), $academico);
		$formulario->handleRequest($peticion);

		if ($formulario->isValid()) {
			$academico->upload();
            // Completar las propiedades que el usuario no rellena en el formulario
			//if ($academico->getFile() == NULL)  $academico->setPath('user.jpeg');
			$em->persist($academico);

			//si el usuario cambio la cédula modificamos el username y password 
			if ($ci != $academico->getCi()){
				$repository = $this->getDoctrine()->getRepository('CituaoUsuarioBundle:Usuario');
				$usuario = $repository->findOneBy(array('username' => $ci));
				
				$usuario->setUsername($academico->getCi());
				$usuario->setPassword($academico->getCi());
				$usuario->setSalt(md5(time()));

				//codificamos el password			
				$encoder = $this->get('security.encoder_factory')->getEncoder($usuario);
				$passwordCodificado = $encoder->encodePassword($usuario->getPassword(), $usuario->getSalt());
				$usuario->setPassword($passwordCodificado);
				$em->persist($usuario);
			}

			$em->flush();

            // Crear un mensaje flash para notificar al usuario que se ha modificado correctamente
			$this->get('session')->getFlashBag()->add('info',
				'¡Listo, se ha modificado el asesor académico!'
				);
			return $this->redirect($this->generateUrl('cituao_coord_academicos'));
		}
		
						//buscamos el programa
		$user = $this->get('security.context')->getToken()->getUser();
		$coordinador =  $user->getUsername();
		$repository = $this->getDoctrine()->getRepository('CituaoUsuarioBundle:Programa');
		$programa = $repository->findOneByCoordinador($coordinador);
		
		return $this->render('CituaoCoordBundle:Default:academico.html.twig', array('formulario' => $formulario->createView(), 'academico' => $academico, 'programa' => $programa ));
		
	}

	/********************************************************/
	//Listar los centros de practicas registrados en la base de datos segun la coordinacion
	/********************************************************/	
	public function centrosAction(){
	
		$user = $this->get('security.context')->getToken()->getUser();
		$coordinador =  $user->getUsername();

		$repository = $this->getDoctrine()->getRepository('CituaoUsuarioBundle:Programa');
		$programa = $repository->findOneByCoordinador($coordinador);

		$listaCentros = $programa->getCentros();

		if ($listaCentros->count() == 0) {
			$msgerr = array('descripcion'=>'No hay centros de práctica registrados!','id'=>'1');
		}else{
			$msgerr = array('descripcion'=>'','id'=>'0');
		}
		
		//buscamos el programa
		return $this->render('CituaoCoordBundle:Default:centros.html.twig', array('listaCentros' => $listaCentros, 'msgerr' => $msgerr, 'programa' => $programa));
	}

	/***************************************************************************/
	//Muestra formulario para registrar un nuevo centro de practicas en la base de datos
	/***************************************************************************/		
	public function registrarCentroAction(){
		$peticion = $this->getRequest();
		$em = $this->getDoctrine()->getManager();
		$centro = new Centro();
		$formulario = $this->createForm(new CentroType(), $centro);
		$formulario->handleRequest($peticion);
		if ($formulario->isValid()) {

			// Completar las propiedades que el usuario no rellena en el formulario
						// buscamos el programa para asignarlo al programa academico
			$user = $this->get('security.context')->getToken()->getUser();
			$coordinador =  $user->getUsername();
			$repository = $this->getDoctrine()->getRepository('CituaoUsuarioBundle:Programa');
			$programa = $repository->findOneByCoordinador($coordinador);
			$centro->setPrograma($programa);
			
			$em->persist($centro);
			$em->flush();

			// Crear un mensaje flash para notificar al usuario
			$this->get('session')->getFlashBag()->add('info',
				'¡Listo centro de práctica registrado!'
			);
			return $this->redirect($this->generateUrl('cituao_coord_centros'));
		}
				//buscamos el programa
		$user = $this->get('security.context')->getToken()->getUser();
		$coordinador =  $user->getUsername();
		$repository = $this->getDoctrine()->getRepository('CituaoUsuarioBundle:Programa');
		$programa = $repository->findOneByCoordinador($coordinador);
		
		return $this->render('CituaoCoordBundle:Default:registrarcentro.html.twig', array('formulario' => $formulario->createView(), 'programa' => $programa));
	}

	/********************************************************/
	//Muestra y modifica un centro de practica registrado en la base de datos
	/********************************************************/		
	public function centroAction($codigo){
		$peticion = $this->getRequest();
		$em = $this->getDoctrine()->getManager();

		$repository = $this->getDoctrine()->getRepository('CituaoCoordBundle:Centro');
		$centro = $repository->findOneBy(array('id' => $codigo));
		
		$formulario = $this->createForm(new CentroType(), $centro);
		$formulario->handleRequest($peticion);
		
		if ($formulario->isValid()) {
            // Completar las propiedades que el usuario no rellena en el formulario
			$em->persist($centro);
			$em->flush();

			// Crear un mensaje flash para notificar al usuario
			$this->get('session')->getFlashBag()->add('info',
				'¡Listo centro de práctica modificado!'
			);
			return $this->redirect($this->generateUrl('cituao_coord_centros'));
		}
		
		//buscamos el programa
		$user = $this->get('security.context')->getToken()->getUser();
		$coordinador =  $user->getUsername();
		$repository = $this->getDoctrine()->getRepository('CituaoUsuarioBundle:Programa');
		$programa = $repository->findOneByCoordinador($coordinador);
	
		return $this->render('CituaoCoordBundle:Default:centro.html.twig', array('formulario' => $formulario->createView(), 'centro' => $centro, 'programa' => $programa ));
	}
	
	/*************************************************
	funcion que recibe la peticion ajax por jquery	y	 
	retorno un json los asesores externos del centro de practicas
	seleccionado en el select 
	**************************************************/
	public function obtenerexternosporcentroAction(){
		$request = $this->getRequest();
		$id_centro = $request->request->get('cod_centro');

		$em = $this->getDoctrine()->getManager();
		$repository = $this->getDoctrine()->getRepository('CituaoCoordBundle:Centro');
		$centro = $repository->findOneBy(array('id' => $id_centro));
		$externos = $centro->getExternos();

		if(!$externos){
			$exception = array('codigo' => '999', 'message' => 'no hay registros');
		}
		else{
			$exception = array('codigo' => '000', 'message' => 'si hay registros');
		}
		//return $this->render('CituaoCoordBundle:Default:prueba.html.twig', array('externos' => $externos, 'exception' => $exception ));
		
		foreach ($externos as $e){
			$fexternos[] = array("id" => $e->getId(), "nombres" => $e->getNombres(), "apellidos" => $e->getApellidos());
		}

		$serializer = new Serializer(array(new GetSetMethodNormalizer()), array('json' => new 
			JsonEncoder()));
		$json = $serializer->serialize($fexternos, 'json');

		return new Response($json);

	}

	//*****************************************************************/
	//Mostrar el cronograma comun entre practicante y academico
    /******************************************************************/	
    public function cronogramapracticanteAction($id){
		//busco al practicante
    	$repository = $this->getDoctrine()->getRepository('CituaoCoordBundle:Practicante');
    	$practicante = $repository->findOneBy(array('id' => $id));

		//busco al academico
    	$repository = $this->getDoctrine()->getRepository('CituaoAcademicoBundle:Academico');
    	$academico = $repository->findOneBy(array('id' => $practicante->getAcademico()->getId()));

		//buscamos el cronograma del asesor academico
    	$em = $this->getDoctrine()->getManager();
    	$query = $em->createQuery(
    		'SELECT c FROM CituaoAcademicoBundle:Cronograma c WHERE c.academico =:id_aca AND c.practicante =:id_pra');
    	$query->setParameter('id_aca',$academico->getId());
    	$query->setParameter('id_pra',$id);
    	$cronograma = $query->getOneOrNullResult();

		//buscamos el cronograma del asesor externo
    	$query = $em->createQuery(
    		'SELECT c FROM CituaoExternoBundle:Cronogramaexterno c WHERE c.externo =:id_ext AND c.practicante =:id_pra');
    	$query->setParameter('id_ext',$practicante->getExterno()->getId());
    	$query->setParameter('id_pra',$id);
    	$cronogramaexterno = $query->getOneOrNullResult();

		//buscamos el programa
		$user = $this->get('security.context')->getToken()->getUser();
		$coordinador =  $user->getUsername();
		$repository = $this->getDoctrine()->getRepository('CituaoUsuarioBundle:Programa');
		$programa = $repository->findOneByCoordinador($coordinador);
		
    	return $this->render('CituaoCoordBundle:Default:cronogramapracticante.html.twig', array('c' => $cronograma, 'p' => $practicante, 'e' => $cronogramaexterno, 'programa' => $programa ));
    }

		//***************************************************************
	//mostrar la asesoria solicitada
	//***************************************************************
    public function consultarAsesoriaAction($id, $numase) {
    	$peticion = $this->getRequest();
    	$em = $this->getDoctrine()->getManager();

    	$repository = $this->getDoctrine()->getRepository('CituaoCoordBundle:Practicante');
    	$practicante = $repository->findOneBy(array('id' => $id));

		//buscamos la asesoría
		switch($numase){
			case 1:
				$qString = 'SELECT a FROM CituaoCoordBundle:Asesoria a WHERE a.academico =:id_aca AND a.practicante =:id_pra';
				break;
			case 2:
				$qString = 'SELECT a FROM CituaoCoordBundle:Asesoria2 a WHERE a.academico =:id_aca AND a.practicante =:id_pra';
				break;
			case 3:
				$qString = 'SELECT a FROM CituaoCoordBundle:Asesoria3 a WHERE a.academico =:id_aca AND a.practicante =:id_pra';
				break;
			case 4:
				$qString = 'SELECT a FROM CituaoCoordBundle:Asesoria4 a WHERE a.academico =:id_aca AND a.practicante =:id_pra';
				break;
			case 5:
				$qString = 'SELECT a FROM CituaoCoordBundle:Asesoria5 a WHERE a.academico =:id_aca AND a.practicante =:id_pra';
				break;
			case 6:
				$qString = 'SELECT a FROM CituaoCoordBundle:Asesoria6 a WHERE a.academico =:id_aca AND a.practicante =:id_pra';
				break;
			case 7:
				$qString = 'SELECT a FROM CituaoCoordBundle:Asesoria7 a WHERE a.academico =:id_aca AND a.practicante =:id_pra';
				break;
		}
		
		$query = $em->createQuery($qString);
    	$query->setParameter('id_aca',$practicante->getAcademico()->getId());
    	$query->setParameter('id_pra',$id);

    	$asesoria = $query->getOneOrNullResult();	

		//nos traemos la documentacion
    	switch($numase){
    		case 1: 
    		$docase = $asesoria->getDocAsesor1();
    		$docpra = $asesoria->getDocPracticante1();
    		break;
    		case 2: 
    		$docase = $asesoria->getDocAsesor2();
    		$docpra = $asesoria->getDocPracticante2();
    		break;
    		case 3: 
    		$docase = $asesoria->getDocAsesor3();
    		$docpra = $asesoria->getDocPracticante3();
    		break;
    		case 4: 
    		$docase = $asesoria->getDocAsesor4();
    		$docpra = $asesoria->getDocPracticante4();
    		break;
    		case 5: 
    		$docase = $asesoria->getDocAsesor5();
    		$docpra = $asesoria->getDocPracticante5();
    		break;
    		case 6: 
    		$docase = $asesoria->getDocAsesor6();
    		$docpra = $asesoria->getDocPracticante6();
    		break;
    		case 7: 
    		$docase = $asesoria->getDocAsesor7();
    		$docpra = $asesoria->getDocPracticante7();
    		break;
    	}

    	$datos = array('id' => $id, 'numase' => $numase, 'docase' => $docase, 'docpra' => $docpra);
		
				//buscamos el programa
		$user = $this->get('security.context')->getToken()->getUser();
		$coordinador =  $user->getUsername();
		$repository = $this->getDoctrine()->getRepository('CituaoUsuarioBundle:Programa');
		$programa = $repository->findOneByCoordinador($coordinador);
		
    	return $this->render('CituaoCoordBundle:Default:asesoria.html.twig', array('datos' => $datos, 'programa' => $programa));
    }


	//*********************************************
	//Muestra el comentario de la visita de presentación
	//******************************************************
    public function consultarVisitapAction($id){
    	$em = $this->getDoctrine()->getManager();

    	$repository = $this->getDoctrine()->getRepository('CituaoCoordBundle:Practicante');
    	$practicante = $repository->findOneBy(array('id' => $id));

		//buscamos la asesoría
    	$query = $em->createQuery(
    		'SELECT a FROM CituaoAcademicoBundle:Cronograma a WHERE a.academico =:id_aca AND a.practicante =:id_pra');
    	$query->setParameter('id_aca',$practicante->getAcademico()->getId());
    	$query->setParameter('id_pra',$id);

    	$cronograma = $query->getOneOrNullResult();	

    	$datos = array('id' => $id, 'comentario' => $cronograma->getComentario());
		
				//buscamos el programa
		$user = $this->get('security.context')->getToken()->getUser();
		$coordinador =  $user->getUsername();
		$repository = $this->getDoctrine()->getRepository('CituaoUsuarioBundle:Programa');
		$programa = $repository->findOneByCoordinador($coordinador);
		
    	return $this->render('CituaoCoordBundle:Default:visita.html.twig', array('datos' => $datos, 'programa' => $programa));
    }

		//*************************************************************
	//Registrar comentario a la evaluacion 1 efectuada por el asesor externo
	//*************************************************************
    public function consultarEvaluacionAction($id, $numeva){

    	$peticion = $this->getRequest();
    	$em = $this->getDoctrine()->getManager();

		//buscamos el practicante oara accesar el id del asesor externo
    	$repository = $this->getDoctrine()->getRepository('CituaoCoordBundle:Practicante');
    	$practicante = $repository->findOneBy(array('id' => $id));


		//buscamos la evaluacion
    	if ($numeva == 1){
    		$repository = $this->getDoctrine()->getRepository('CituaoExternoBundle:Evaluacion1');
    		$evaluacion = $repository->findOneBy(array('practicante' => $id));
    	}else{
    		$repository = $this->getDoctrine()->getRepository('CituaoExternoBundle:Evaluacion2');
    		$evaluacion = $repository->findOneBy(array('practicante' => $id));
    	}

		//buscamos el programa
		$user = $this->get('security.context')->getToken()->getUser();
		$coordinador =  $user->getUsername();
		$repository = $this->getDoctrine()->getRepository('CituaoUsuarioBundle:Programa');
		$programa = $repository->findOneByCoordinador($coordinador);
		
    	$datos = array('id' => $id, 'numeva' => $numeva);
    	if ($numeva == 1)
    		return $this->render('CituaoCoordBundle:Default:evaluacion.html.twig', array('datos' => $datos, 'formulario' => $evaluacion, 'programa' => $programa));
    	else
    		return $this->render('CituaoCoordBundle:Default:evaluacion2.html.twig', array('datos' => $datos, 'formulario' => $evaluacion, 'programa' => $programa));
    }

	//******************************************************
	//Mostrar el informe de gestión cuali cuanti sea 1,2,3
	//******************************************************	
    public function consultarGestionAction($id, $numges){
    	$peticion = $this->getRequest();
    	$em = $this->getDoctrine()->getManager();

		// buscamos el practicante
    	$repository = $this->getDoctrine()->getRepository('CituaoCoordBundle:Practicante');
    	$practicante = $repository->findOneBy(array('id' => $id));

		//determinamos si ya fue registrado el informe en la base de datos si es positivo es una actualizacion
    	$sw = false;
    	switch($numges){
    		case 1:
    		if($practicante->getlistoGestion1()) $sw=true;
    		break;
    		case 2:
    		if($practicante->getlistoGestion2()) $sw=true;
    		break;
    		case 3:
    		if($practicante->getlistoGestion3()) $sw=true;
    		break;
    	}

		//si ya fue registrado hacemos una instancia del informe cualicuanti para mostrar en formulario
    	if ($sw) {
    		$query = $em->createQuery(
    			'SELECT a FROM CituaoAcademicoBundle:Cualicuanti a WHERE a.practicante =:id_pra  AND a.cualicuanti =:numcua');
    		$query->setParameter('id_pra',$id);
    		$query->setParameter('numcua',$numges);
    		$cualicuanti = $query->getOneOrNullResult();
    	}

    	$datos = array('id' => $id, 'numges' => $numges);
		
				//buscamos el programa
		$user = $this->get('security.context')->getToken()->getUser();
		$coordinador =  $user->getUsername();
		$repository = $this->getDoctrine()->getRepository('CituaoUsuarioBundle:Programa');
		$programa = $repository->findOneByCoordinador($coordinador);
		
    	return $this->render('CituaoCoordBundle:Default:cualicuanti.html.twig', array('gestion' => $cualicuanti, 'datos' => $datos, 'programa' => $programa ));
    }	

	//**************************************************************
	//Mostrar el informe final del asesor académico
	//**************************************************************
    public function consultarInformefinalacademicoAction($id){
    	$em = $this->getDoctrine()->getManager();
    	$repository = $this->getDoctrine()->getRepository('CituaoCoordBundle:Practicante');
    	$practicante = $repository->findOneBy(array('id' => $id));
		//buscamos el informe  para actualizar 
    	$query = $em->createQuery(
    		'SELECT i FROM CituaoAcademicoBundle:Informefinalacademico i WHERE i.practicante =:id_pra ');
    	$query->setParameter('id_pra',$id);

    	$informe = $query->getOneOrNullResult();
		//si no hay informes creamos una instancia de informe final

    	$datos = array('id' => $id);
				//buscamos el programa
		$user = $this->get('security.context')->getToken()->getUser();
		$coordinador =  $user->getUsername();
		$repository = $this->getDoctrine()->getRepository('CituaoUsuarioBundle:Programa');
		$programa = $repository->findOneByCoordinador($coordinador);
		
    	return $this->render('CituaoCoordBundle:Default:informefinalacademico.html.twig', array(
    		'informe' => $informe, 'datos' => $datos, 'programa' => $programa
    		));
    }

    public function consultarActaAction($id){
    	$em = $this->getDoctrine()->getManager();
    	$repository = $this->getDoctrine()->getRepository('CituaoCoordBundle:Practicante');
    	$practicante = $repository->findOneBy(array('id' => $id));
		//buscamos el informe  para actualizar 
    	$query = $em->createQuery(
    		'SELECT i FROM CituaoExternoBundle:Acta i WHERE i.practicante =:id_pra ');
    	$query->setParameter('id_pra',$id);

    	$acta = $query->getOneOrNullResult();
		//si no hay informes creamos una instancia de informe final

    	$datos = array('id' => $id);
				//buscamos el programa
		$user = $this->get('security.context')->getToken()->getUser();
		$coordinador =  $user->getUsername();
		$repository = $this->getDoctrine()->getRepository('CituaoUsuarioBundle:Programa');
		$programa = $repository->findOneByCoordinador($coordinador);
		
    	return $this->render('CituaoCoordBundle:Default:acta.html.twig', array(
    		'e' => $acta, 'datos' => $datos, 'programa' => $programa
    		));
    }

	//**************************************************************
	//Mostrar el informe final del practicante
	//**************************************************************
    public function consultarInformefinalpracticanteAction($id){
    	$em = $this->getDoctrine()->getManager();
    	$repository = $this->getDoctrine()->getRepository('CituaoCoordBundle:Practicante');
    	$practicante = $repository->findOneBy(array('id' => $id));
		//buscamos el informe  para actualizar 
    	$query = $em->createQuery(
    		'SELECT i FROM CituaoPracticanteBundle:Informefinalpracticante i WHERE i.practicante =:id_pra ');
    	$query->setParameter('id_pra',$id);

    	$informe = $query->getOneOrNullResult();
		//si no hay informes creamos una instancia de informe final

    	$datos = array('id' => $id);
				//buscamos el programa
		$user = $this->get('security.context')->getToken()->getUser();
		$coordinador =  $user->getUsername();
		$repository = $this->getDoctrine()->getRepository('CituaoUsuarioBundle:Programa');
		$programa = $repository->findOneByCoordinador($coordinador);
		
    	return $this->render('CituaoCoordBundle:Default:informefinalpracticante.html.twig', array(
    		'informe' => $informe, 'datos' => $datos, 'programa' => $programa, 'practicante' => $practicante
    		));
    }
	
	//**************************************************************************
	//Elimina el cornograma asignado a un practicante
	//**************************************************************************
	public function cronogramaEliminarAction($id){
	
		//borramos el cronograma asignado al asesor academico
		$em = $this->getDoctrine()->getManager();
		$query = $em->createQuery(
				'DELETE CituaoAcademicoBundle:Cronograma c
				WHERE c.practicante = :id')
				->setParameter('id', $id);
		$query->execute();
		
		//borramos el cronograma asignado al asesor externo
		$query = $em->createQuery(
				'DELETE CituaoExternoBundle:Cronogramaexterno c
				WHERE c.practicante = :id')
				->setParameter('id', $id);
		$query->execute();
		
		//colocamos null en los campos externo, academico y centro del registro del practicante
		$query = $em->createQuery(
				'UPDATE CituaoCoordBundle:Practicante c
				SET c.academico = NULL, c.externo = NULL, c.centro = NULL, c.fechaIniciacion = NULL, c.estado =0
				WHERE c.id = :id')
				->setParameter('id', $id);
		$query->execute();
		
		
		//le colocamos estado false no tiene cronograma
		$repository=$this->getDoctrine()->getRepository('CituaoCoordBundle:Practicante');
		$practicante=$repository->findOneBy(array('id'=>$id));
		$practicante->setEstado(0);
		$em->persist($practicante);
		
		
		//inactivamos el usuario
		$repository=$this->getDoctrine()->getRepository('CituaoUsuarioBundle:Usuario');
		$usuario=$repository->findOneBy(array('username'=>$practicante->getCodigo()));
		$usuario->setIsActive(false);
		
		
		/*
		//muestra la lista de practicantes
		$document = new Document();
		$form = $this->createFormBuilder($document)
		->add('file')
		->add('name')
		->getForm();

		//buscamos el programa
		$user = $this->get('security.context')->getToken()->getUser();
		$coordinador =  $user->getUsername();
		$repository = $this->getDoctrine()->getRepository('CituaoUsuarioBundle:Programa');
		$programa = $repository->findOneByCoordinador($coordinador);

		//obtenemos los practicantes
		$listaPracticantes = $programa->getPracticantes();

		if ($listaPracticantes->count() == 0) {
			$msgerr = array('descripcion'=>'No hay practicantes registrados!','id'=>'1');
		}else{
			$msgerr = array('descripcion'=>'','id'=>'0');
		}
		return $this->render('CituaoCoordBundle:Default:practicantes.html.twig', array('form' => $form->createView() , 'listaPracticantes' => $listaPracticantes, 'programa' => $programa, 'msgerr' => $msgerr));
		*/
		return $this->redirect($this->generateUrl('cituao_coord_practicantes'));
	}
	
	//*************************************************************************************************************
	//listar estudiantes segun filtro aplicado por periodo academico y estado del practicante
	//*************************************************************************************************************
	public function consultarPracticantesAction($p, $e) 
	{
		$document = new Document();
		$form = $this->createFormBuilder($document)
		->add('file')
		->add('name')
		->getForm();

		//buscamos el programa
		$user = $this->get('security.context')->getToken()->getUser();
		$coordinador =  $user->getUsername();
		$repository = $this->getDoctrine()->getRepository('CituaoUsuarioBundle:Programa');
		$programa = $repository->findOneByCoordinador($coordinador);

		//buscamos los periodos y el periodo actual
		$repository = $this->getDoctrine()->getRepository('CituaoUsuarioBundle:Periodo');
		$query = $repository->createQueryBuilder('p')
				->orderBy('p.id','DESC')
				->getQuery();
		$periodos = $query->getResult();

		foreach($periodos as $pdo){
			if ($pdo->getId() == $p){
				$dataperiodo = array('id' => $pdo->getId(), 'nombre'=> $pdo->getNombre());
				break;
			}
		} 		

		//obtenemos los practicantes
		$repository = $this->getDoctrine()->getRepository('CituaoCoordBundle:Practicante');
		$query = $repository->createQueryBuilder('p')
				->where('p.programa = :id_programa')
				->andWhere('p.periodo = :id_periodo')
				->andWhere('p.estado = :estado')
				->setParameter('id_programa', $programa->getId())
				->setParameter('id_periodo', $p)
				->setParameter('estado',$e)
				->orderBy('p.apellidos', 'ASC')
				->getQuery();
				
		//->setParameter('id_programa', $programa->getId())
				$listaPracticantes = $query->getResult();

		if ($listaPracticantes == NULL) {
			$msgerr = array('descripcion'=>'No hay practicantes registrados!','id'=>'1');
		}else{
			$msgerr = array('descripcion'=>'','id'=>'0');
		}
		$filtro = array('periodo'=> $p, 'estado'=> $e);
		return $this->render('CituaoCoordBundle:Default:practicantes.html.twig', array('dataperiodo'=> $dataperiodo, 'filtro' => $filtro, 'periodos' => $periodos, 'form' => $form->createView() , 'listaPracticantes' => $listaPracticantes, 'programa' => $programa, 'msgerr' => $msgerr));
	}
	
	//****************************************************
	//listar practicantes por periodo
	//****************************************************
	public function practicantesPeriodoAction($p)
	{
		$document = new Document();
		$form = $this->createFormBuilder($document)
		->add('file')
		->add('name')
		->getForm();

		//buscamos el programa
		$user = $this->get('security.context')->getToken()->getUser();
		$coordinador =  $user->getUsername();
		$repository = $this->getDoctrine()->getRepository('CituaoUsuarioBundle:Programa');
		$programa = $repository->findOneByCoordinador($coordinador);

		//buscamos los periodos y el periodo actual
		$repository = $this->getDoctrine()->getRepository('CituaoUsuarioBundle:Periodo');
		$query = $repository->createQueryBuilder('p')
				->orderBy('p.id','DESC')
				->getQuery();
		$periodos = $query->getResult();

		foreach($periodos as $pdo){
			if ($pdo->getId() == $p){
				$dataperiodo = array('id' => $pdo->getId(), 'nombre' => $pdo->getNombre());
				break;
			}
		}	

		
		$e = 1; //buscamos por defecto los que estan en proceso
		
		//obtenemos los practicantes
		$repository = $this->getDoctrine()->getRepository('CituaoCoordBundle:Practicante');
		$query = $repository->createQueryBuilder('p')
				->where('p.programa = :id_programa')
				->andWhere('p.periodo = :id_periodo')
				->andWhere('p.estado = :estado')
				->setParameter('id_programa', $programa->getId())
				->setParameter('id_periodo', $p)
				->setParameter('estado',$e)
				->orderBy('p.apellidos', 'ASC')
				->getQuery();
				
		//->setParameter('id_programa', $programa->getId())
				$listaPracticantes = $query->getResult();

		if ($listaPracticantes == NULL) {
			$msgerr = array('descripcion'=>'No hay practicantes registrados!','id'=>'1');
		}else{
			$msgerr = array('descripcion'=>'','id'=>'0');
		}
		$filtro = array('periodo'=> $p, 'estado'=> $e);
		return $this->render('CituaoCoordBundle:Default:practicantes.html.twig', array('filtro' => $filtro, 'periodos' => $periodos, 'form' => $form->createView() , 'listaPracticantes' => $listaPracticantes, 'programa' => $programa, 'msgerr' => $msgerr, 'dataperiodo' => $dataperiodo ));
	}

	//*********************************************************************************
	//Mostrar los practicantes con retrasos en la entrega 
	//*********************************************************************************
	public function practicantesRetrasoAction(){
		//buscamos el programa
		$user = $this->get('security.context')->getToken()->getUser();
		$coordinador =  $user->getUsername();
		$repository = $this->getDoctrine()->getRepository('CituaoUsuarioBundle:Programa');
		$programa = $repository->findOneByCoordinador($coordinador);

		//buscamos los periodos y el periodo actual
		$repository = $this->getDoctrine()->getRepository('CituaoUsuarioBundle:Periodo');
		$query = $repository->createQueryBuilder('p')
				->orderBy('p.id','DESC')
				->getQuery();
		$periodos = $query->getResult();
		foreach ($periodos as $periodoActual){
			break;
		}
		
		//obtenemos los practicantes sin importar el periodo
		//->andWhere('p.periodo = :id_periodo') ->setParameter('id_periodo', $periodoActual->getId())
		$repository = $this->getDoctrine()->getRepository('CituaoCoordBundle:Practicante');
		$query = $repository->createQueryBuilder('p')
				->where('p.programa = :id_programa')
				->andWhere('p.estado = 1')
				->setParameter('id_programa', $programa->getId())
				->getQuery();
				
		//->setParameter('id_programa', $programa->getId())
				$listaPracticantes = $query->getResult();

		if ($listaPracticantes == NULL) {
			$msgerr = array('descripcion'=>'No hay practicantes registrados!','id'=>'1');
		}else{
			$msgerr = array('descripcion'=>'','id'=>'0');
		}
		$filtro = array('periodo' => $periodoActual->getId(), 'estado' => '1');
		//filtramos los practicantes que estan en retraso en la entrega de actividades
		$hoy = new DateTime();
		$retrasos = 0;
		$i=0;
		$retrasados = array();
		foreach ($listaPracticantes as $p){
			if ($p->getFechaAsesoria1() < $hoy && $p->getListoAsesoria1()  == false) $retrasos++;
			if ($p->getFechaAsesoria2() < $hoy && $p->getListoAsesoria2() == false) $retrasos++;
			if ($p->getFechaAsesoria3() < $hoy && $p->getListoAsesoria3() == false) $retrasos++;
			if ($p->getFechaAsesoria4() < $hoy && $p->getListoAsesoria4() == false) $retrasos++;
			if ($p->getFechaAsesoria5() < $hoy && $p->getListoAsesoria5() == false) $retrasos++;
			if ($p->getFechaAsesoria6() < $hoy && $p->getListoAsesoria6() == false) $retrasos++;
			if ($p->getFechaAsesoria7() < $hoy && $p->getListoAsesoria7() == false) $retrasos++;
			if ($p->getFechaInformeGestion1() < $hoy && $p->getListoGestion1() == false) $retrasos++;
			if ($p->getFechaInformeGestion2() < $hoy && $p->getListoGestion2() == false) $retrasos++;
			if ($p->getFechaInformeGestion3() < $hoy && $p->getListoGestion3() == false) $retrasos++;
			if ($p->getFechaInformeFinal() < $hoy && $p->getListoInformeFinal() == false) $retrasos++;
			//solo para practicantes del area organizacional
			$area = $p->getArea();
			if($area == 2 || $area == 3){
				if ($p->getListoInformeFinal() && $p->getListoProyecto() == false) $retrasos++;
			}
			//creamos el registro 
			if ($retrasos > 0){
				$retrasados[$i] = array('id' => $p->getId() ,'ci' => $p->getCi(), 'nombres' => $p->getNombres(), 'apellidos' => $p->getApellidos(), 'path' => $p->getPath(), 'emailInstitucional' => $p->getEmailInstitucional(), 'emailPersonal' => $p->getEmailPersonal(), 'retrasos' => $retrasos);
			}
			$retrasos=0;
			$i++;
		}
		return $this->render('CituaoCoordBundle:Default:practicantesenretraso.html.twig', array('periodos' => $periodos, 'listaPracticantes' => $retrasados, 'programa' => $programa, 'msgerr' => $msgerr, 'lista' => $filtro));
	}
	
	//******************************************************************
	//Muestra los asesores academicos que presentan retraso
	//*******************************************************************
	public function academicosRetrasoAction(){
		$user = $this->get('security.context')->getToken()->getUser();
		$coordinador =  $user->getUsername();

		$repository = $this->getDoctrine()->getRepository('CituaoUsuarioBundle:Programa');
		$programa = $repository->findOneByCoordinador($coordinador);

		$listaAcademicos = $programa->getAcademicos();
		$em = $this->getDoctrine()->getManager();
			
		if ($listaAcademicos->count() == 0) {
			$msgerr = array('descripcion'=>'No hay asesores académicos registrados!','id'=>'1');
			$retrasados = array();

		}else{
			$msgerr = array('descripcion'=>'','id'=>'0');
			$retrasados = array();
			$hayRetraso=false;
			$i=0;
			foreach($listaAcademicos as $academico) {
				$id = $academico->getId();
				$nombre = $academico->getNombres();
				$listaPracticantes = $academico->getPracticantes();
				
				if ($listaPracticantes->count() != 0){
					foreach($listaPracticantes as $practicante) {
						//buscamos el cronograma del asesor academico
						$query = $em->createQuery(
							'SELECT c FROM CituaoAcademicoBundle:Cronograma c WHERE c.academico =:id_aca AND c.practicante =:id_pra');
						$query->setParameter('id_aca',$academico->getId());
						$query->setParameter('id_pra',$practicante->getId());
						$cronograma = $query->getOneOrNullResult();

						$hoy = new DateTime();
						$retrasos = 0;
						
						
						if ($cronograma->getFechaAsesoria1() < $hoy && $cronograma->getListoAsesoria1()  == null) $retrasos++;
						if ($cronograma->getFechaAsesoria2() < $hoy && $cronograma->getListoAsesoria2()  == null) $retrasos++;
						if ($cronograma->getFechaAsesoria3() < $hoy && $cronograma->getListoAsesoria3()  == null) $retrasos++;
						if ($cronograma->getFechaAsesoria4() < $hoy && $cronograma->getListoAsesoria4()  == null) $retrasos++;
						if ($cronograma->getFechaAsesoria5() < $hoy && $cronograma->getListoAsesoria5()  == null) $retrasos++;
						if ($cronograma->getFechaAsesoria6() < $hoy && $cronograma->getListoAsesoria6()  == null) $retrasos++;
						if ($cronograma->getFechaAsesoria7() < $hoy && $cronograma->getListoAsesoria7()  == null) $retrasos++;
						if ($cronograma->getFechaVisitaP() < $hoy && $cronograma->getListoVisitaP()  == null) $retrasos++;
						if ($cronograma->getFechaInformeGestion1() < $hoy && $cronograma->getListoGestion1() == null) $retrasos++;
						if ($cronograma->getFechaInformeGestion2() < $hoy && $cronograma->getListoGestion2() == null) $retrasos++;
						if ($cronograma->getFechaInformeGestion3() < $hoy && $cronograma->getListoGestion3() == null) $retrasos++;
						if ($cronograma->getFechaEvaluacion1() < $hoy && $cronograma->getListoEvaluacion1() == null) $retrasos++;
						if ($cronograma->getFechaEvaluacion2() < $hoy && $cronograma->getListoEvaluacion2() == null) $retrasos++;
						if ($cronograma->getFechaEvaluacionFinal() < $hoy && $cronograma->getListoEvaluacionFinal() == null) $retrasos++;

						if ($retrasos > 0){
							$retrasados[$i] = array('id' => $practicante->getId() ,'ci' => $practicante->getCi(), 'nombres' => $practicante->getNombres(), 'apellidos' => $practicante->getApellidos(), 'path' => $practicante->getPath(), 'emailInstitucional' => $practicante->getEmailInstitucional(), 'emailPersonal' => $practicante->getEmailPersonal(), 'retrasos' => $retrasos);
							$hayRetraso = true;
							$i++;
						}
						$retrasos=0;
					}
				}
			}
			if (!$hayRetraso){
				$msgerr = array('descripcion'=>'¡Asesores académicos no presentan demoras!','id'=>'1');
			}else{
				$msgerr = array('descripcion'=>'','id'=>'0');
			}
		}
		return $this->render('CituaoCoordBundle:Default:academicosenretraso.html.twig', array('listaPracticantes' => $retrasados, 'programa' => $programa, 'msgerr' => $msgerr));
	}

	//******************************************************************
	//Muestra los asesores academicos que presentan retraso
	//*******************************************************************
	public function practicantesAsesorAction($ci){
		$em = $this->getDoctrine()->getManager();
		
		$user = $this->get('security.context')->getToken()->getUser();

		$coordinador =  $user->getUsername();
		$repository = $this->getDoctrine()->getRepository('CituaoUsuarioBundle:Programa');
		$programa = $repository->findOneByCoordinador($coordinador);


		$repository = $this->getDoctrine()->getRepository('CituaoAcademicoBundle:Academico');
		$academico = $repository->findOneBy(array('ci' => $ci));

		$listaPracticantes = $academico->getPracticantes();
		
		//filtramos los practicantes que han culminado
		
		$i=0;
		foreach ($listaPracticantes as $p) {
			if ($p->getEstado() == 2) {
				$listaPracticantes->remove($i);
			}
			$i++;
		}
		$msgerr = array('descripcion'=>'No tiene practicantes','id'=>'0');	
		return $this->render('CituaoCoordBundle:Default:practicantesasesor.html.twig', array('listaPracticantes' => $listaPracticantes, 'msgerr' => $msgerr, 'programa' => $programa, 'academico' => $academico));
	}
	
	//******************************************************************
	//Muestra los asesores academicos que presentan retraso
	//*******************************************************************
	public function enviarCorreosPracticantesAction(){
		//buscamos el programa
		$user = $this->get('security.context')->getToken()->getUser();
		$coordinador =  $user->getUsername();
		$repository = $this->getDoctrine()->getRepository('CituaoUsuarioBundle:Programa');
		$programa = $repository->findOneByCoordinador($coordinador);

		$email_from=$programa->getEmail();

		//buscamos los periodos y el periodo actual
		$repository = $this->getDoctrine()->getRepository('CituaoUsuarioBundle:Periodo');
		$query = $repository->createQueryBuilder('p')
				->orderBy('p.id','DESC')
				->getQuery();
		$periodos = $query->getResult();
		foreach ($periodos as $periodoActual){
			break;
		}
		
		//obtenemos los practicantes sin importar el periodo
		//->andWhere('p.periodo = :id_periodo') ->setParameter('id_periodo', $periodoActual->getId())
		$repository = $this->getDoctrine()->getRepository('CituaoCoordBundle:Practicante');
		$query = $repository->createQueryBuilder('p')
				->where('p.programa = :id_programa')
				->andWhere('p.estado = 1')
				->setParameter('id_programa', $programa->getId())
				->getQuery();
				
		//->setParameter('id_programa', $programa->getId())
				$listaPracticantes = $query->getResult();

		if ($listaPracticantes == NULL) {
			$msgerr = array('descripcion'=>'No hay practicantes registrados!','id'=>'1');
		}else{
			$msgerr = array('descripcion'=>'','id'=>'0');
		}
		$filtro = array('periodo' => $periodoActual->getId(), 'estado' => '1');
		//filtramos los practicantes que estan en retraso en la entrega de actividades
		$hoy = new DateTime();
		$retrasos = 0;
		$i=0;
		
		$message = \Swift_Message::newInstance();
		$message->setSubject('Notificación - Práctica profesional');
		$message->setFrom(array($email_from=>'Coord. de Práctica profesional'));
		$message->setContentType("text/html");
		$message->setBody($this->renderView('CituaoCoordBundle:Default:email.html.twig'),'text/html');
		foreach ($listaPracticantes as $p){
			if ($p->getEmailPersonal() != null){
				if ($p->getFechaAsesoria1() < $hoy && $p->getListoAsesoria1()  == false) $retrasos++;
				if ($p->getFechaAsesoria2() < $hoy && $p->getListoAsesoria2() == false) $retrasos++;
				if ($p->getFechaAsesoria3() < $hoy && $p->getListoAsesoria3() == false) $retrasos++;
				if ($p->getFechaAsesoria4() < $hoy && $p->getListoAsesoria4() == false) $retrasos++;
				if ($p->getFechaAsesoria5() < $hoy && $p->getListoAsesoria5() == false) $retrasos++;
				if ($p->getFechaAsesoria6() < $hoy && $p->getListoAsesoria6() == false) $retrasos++;
				if ($p->getFechaAsesoria7() < $hoy && $p->getListoAsesoria7() == false) $retrasos++;
				if ($p->getFechaInformeGestion1() < $hoy && $p->getListoGestion1() == false) $retrasos++;
				if ($p->getFechaInformeGestion2() < $hoy && $p->getListoGestion2() == false) $retrasos++;
				if ($p->getFechaInformeGestion3() < $hoy && $p->getListoGestion3() == false) $retrasos++;
				if ($p->getFechaInformeFinal() < $hoy && $p->getListoInformeFinal() == false) $retrasos++;
				//solo para practicantes del area organizacional
				$area = $p->getArea();
				if($area == 2 || $area == 3){
					if ($p->getListoInformeFinal() && $p->getListoProyecto() == false) $retrasos++;
				}

				//creamos el registro
				//$practicante->getEmailInstitucional(), 'emailPersonal' => $practicante->getEmailPersonal() 
				if ($retrasos > 0){
					$message->setTo(array($p->getEmailPersonal() => 'Practicante'));
					$this->get('mailer')->send($message);
				}
				$retrasos=0;
				$i++;
			}
		}

		$response = array("code" => 100, "success" => true);
		$serializer = new Serializer(array(new GetSetMethodNormalizer()), array('json' => new 
		JsonEncoder()));
		$json = $serializer->serialize($response, 'json');
		//return new Response($json,200,array('Content-Type'=>'application/json'));
		//return new Response();
		 return new Response(json_encode($response)); 
	}
	
}
