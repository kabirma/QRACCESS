<?php

namespace App\Controller;

use App\Entity\Group;
use App\Entity\Import;
use App\Entity\ImportHeader;
use App\Entity\ImportRow;
use App\Service\ReadFileService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class ImportController extends AbstractController
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    #[Route('user/import/view/{groupId}', name: 'view_import')]
    public function index($groupId)
    {
        $title = "Import";
        $imports = $this->em->getRepository(Import::class)->findBy(['groups' => $groupId]);
        return $this->render('import/index.html.twig', array('title' => $title, 'records' => $imports, 'groupId' => $groupId));
    }

    #[Route('user/import/detail/{importId}', name: 'detail_import')]
    public function detail($importId)
    {
        $title = "Import Detail";
        $imports = $this->em->getRepository(Import::class)->find($importId);
        $headers = $this->em->getRepository(ImportHeader::class)->findOneBy(["import" => $importId]);
        $rows = $this->em->getRepository(ImportRow::class)->findBy(["import" => $importId]);
        return $this->render('import/detail.html.twig', array('title' => $title, 'records' => $imports, 'headers' => $headers, 'rows' => $rows));
    }

    #[Route('user/import/add/{groupId}', name: 'add_import')]
    public function add($groupId)
    {
        $title = 'Add Import';
        return $this->render('import/add.html.twig', ['title' => $title, 'groupId' => $groupId]);
    }

    #[Route('user/import/create', name: 'create_import', methods: ['POST'])]
    public function create(Request $request, SluggerInterface $slugger, ReadFileService $readFile)
    {
        $submittedToken = $request->get('token');
        if ($this->isCsrfTokenValid('create_import', $submittedToken)) {

            $import = new Import();
            $import->setName($request->get('name'));
            $import->setUser($this->getUser());
            $import->setGroups($this->em->getRepository(Group::class)->find($request->get('groupId')));
            $file = $request->files->get('file');
            if ($file) {
                $originalFileName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFileName = $slugger->slug($originalFileName);
                $fileName = $safeFileName . '-' . uniqid() . '.' . $file->guessExtension();

                try {
                    $fullPath = $this->getParameter('kernel.project_dir') . '/public/assets/imports/';
                    $file->move(
                        $fullPath,
                        $fileName
                    );
                    $data = $readFile->read($fullPath . $fileName);
                    $header = new ImportHeader();
                    $header->setField(serialize($data['header']));
                    $header->setImport($import);
                    foreach ($data['rows'] as $item) {
                        $row = new ImportRow();
                        $row->setData(serialize($item));
                        $row->setImport($import);
                        $row->setHeader($header);
                        $this->em->persist($row);

                    }
                } catch (FileException $e) {

                    $this->addFlash(
                        'error',
                        'File Uploading Error!'
                    );

                    $uri = $request->attributes->get("_route");
                    return $this->redirectToRoute($uri);
                }

                $import->setFile("/assets/imports/" . $fileName);
            }

            $this->em->persist($header);
            $this->em->persist($row);
            $this->em->persist($import);
            $this->em->flush();
            $this->addFlash(
                'success',
                'Import Saved Successfully!'
            );
            return $this->redirectToRoute('view_import', ['groupId' => $request->get('groupId')]);
        }

        $this->addFlash(
            'error',
            'Token Mismatch!'
        );
        return $this->redirectToRoute('view_import');
    }


    #[Route('user/import/delete/{id}', name: 'delete_import', methods: ['GET'])]
    public function delete($id)
    {
        $import = $this->em->getRepository(Import::class)->find($id);

        if ($import) {
            $pathToFile = $this->getParameter('kernel.project_dir') . '/public/' . $import->getFile();
            if (file_exists($pathToFile)) {
                unlink($pathToFile);
            }
            $groupId = $import->getGroups()->getId();
            $this->em->remove($import);
            $this->em->flush();
        }
        $this->addFlash(
            'success',
            'Import Deleted Successfully!'
        );
        return $this->redirectToRoute('view_import', ['groupId' => $groupId]);
    }
}
