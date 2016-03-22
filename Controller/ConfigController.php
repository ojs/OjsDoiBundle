<?php
namespace BulutYazilim\OjsDoiBundle\Controller;

use Ojs\CoreBundle\Controller\OjsController as Controller;
use Ojs\JournalBundle\Entity\Journal;
use BulutYazilim\OjsDoiBundle\Entity\CrossrefConfig;
use BulutYazilim\OjsDoiBundle\Form\Type\CrossrefConfigType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ConfigController extends Controller
{
    /**
     * @return Response
     */
    public function editAction()
    {
        $em = $this->getDoctrine()->getManager();
        $journal = $this->get('ojs.journal_service')->getSelectedJournal();

        if (!$this->isGranted('EDIT', $journal) || !$this->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException("You not authorized for this page!");
        }
        $crossrefConfig = $em->getRepository('OjsDoiBundle:CrossrefConfig')->findOneBy(array('journal' => $journal));
        if (!$crossrefConfig) {
            $crossrefConfig = new CrossrefConfig();
        }
        $form = $this->createEditForm($crossrefConfig, $journal);


        $postfixMapping = array(
            '%j' => 'doi.postfix.journal',
            '%v' => 'doi.postfix.volume',
            '%i' => 'doi.postfix.issue',
            '%Y' => 'doi.postfix.year',
            '%a' => 'doi.postfix.article',
            '%p' => 'doi.postfix.page'
        );
        return $this->render(
            'OjsDoiBundle:Config:edit.html.twig',
            [
                'entity' => $crossrefConfig,
                'form' => $form->createView(),
                'postfixMapping' => $postfixMapping
            ]
        );
    }

    /**
     * Creates a form to edit a Lang entity.
     *
     * @param  CrossrefConfig $entity The entity
     * @param  Journal $journal
     * @return \Symfony\Component\Form\Form The form
     */
    private function createEditForm(CrossrefConfig $entity, Journal $journal)
    {
        $form = $this->createForm(
            new CrossrefConfigType(),
            $entity,
            [
                'action' => $this->generateUrl(
                    'okul_bilisim_doi_config_update',
                    ['journalId' => $journal->getId()]
                ),
                'method' => 'PUT',
            ]
        );

        $form->add('submit', 'submit', ['label' => 'Update']);

        return $form;
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function updateAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $journal = $this->get('ojs.journal_service')->getSelectedJournal();

        if (!$this->isGranted('EDIT', $journal) || !$this->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException("You not authorized for this page!");
        }
        $crossrefConfig = $em->getRepository('OjsDoiBundle:CrossrefConfig')->findOneBy(array('journal' => $journal));
        if (!$crossrefConfig) {
            $crossrefConfig = new CrossrefConfig();
        }

        $crossrefConfig->setJournal($journal);

        $form = $this->createEditForm($crossrefConfig, $journal);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $em->persist($crossrefConfig);
            $em->flush();
        }
        $postfixMapping = array(
            '%j' => 'doi.postfix.journal',
            '%v' => 'doi.postfix.volume',
            '%i' => 'doi.postfix.issue',
            '%Y' => 'doi.postfix.year',
            '%a' => 'doi.postfix.article',
            '%p' => 'doi.postfix.page'
        );
        return $this->render(
            'OjsDoiBundle:Config:edit.html.twig',
            [
                'entity' => $crossrefConfig,
                'form' => $form->createView(),
                'postfixMapping' => $postfixMapping
            ]
        );
    }
}
