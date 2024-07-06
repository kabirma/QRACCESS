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

    #[Route('user/confirm/import/{importId}', name: 'confirm_import_view')]
    public function confirm_import_view($importId)
    {
        $title = "Confirm Import Columns";
        $imports = $this->em->getRepository(Import::class)->find($importId);
        $headers = $this->em->getRepository(ImportHeader::class)->findBy(["import" => $importId]);
        $rows = $this->em->getRepository(ImportRow::class)->findBy(["import" => $importId]);
        $data = [];
        foreach ($rows as $item) {
            $data[$item->getId()] = hash('md5',implode(", ", unserialize($item->getData())));
        }

        $sort_array = [];
        $data_header=[];
        foreach ($headers as $header){
            $data_header[$header->getSort()]['field']=$header->getField();
            $data_header[$header->getSort()]['id']=$header->getId();
            $data_header[$header->getSort()]['sort']=$header->getSort();
            $data_header[$header->getSort()]['import_id']=$header->getImport()->getId();
            $data_header[$header->getSort()]['is_exportable']=$header->isIsExportable();
            $data_header[$header->getSort()]['is_displayable']=$header->isIsDisplayable();
            $data_header[$header->getSort()]['contains_qr']=$header->isContainsQr();

            $sort_array[]=$header->getSort();
        }
        ksort($data_header);
        return $this->render('import/column.html.twig', array('title' => $title, 'importId' => $importId, 'records' => $imports, 'headers' => $data_header, 'rows' => $rows, 'qr' => $data,'sort_array'=>$sort_array));
    }

    #[Route('user/confirm/column/import', name: 'confirm_column', methods: ['POST'])]
    public function confirm_column(Request $request, SluggerInterface $slugger, ReadFileService $readFile)
    {
        $submittedToken = $request->get('token');
        if ($this->isCsrfTokenValid('confirm_column', $submittedToken)) {

            $import = $this->em->getRepository(Import::class)->find($request->get('import_id'));

            $header_col = [];
            $cols_to_remove = [];
            foreach($request->get('header_org') as $header_org){
                if(in_array($header_org,$request->get('header'))){
                    $header_col[] = (int)$header_org;
                }else{
                    $header_col[] = 0;
                    $cols_to_remove[] = (int)$header_org;
                }
            }

            $rows = $this->em->getRepository(ImportRow::class)->findBy(["import" => $request->get("import_id")]);
            foreach($rows as $row){
                $data = unserialize($row->getData());
                foreach($header_col as $key => $h_col){
                    if($h_col == 0){
                        unset($data[$key]);
                    }
                }
                $data = array_values($data);
                $data = serialize($data);
                $record = $this->em->getRepository(ImportRow::class)->find($row->getId());
                $record->setData($data);
                $this->em->persist($record);
            }

            $this->em->flush();

            foreach($cols_to_remove as $col_to_remove){
                $importHeader = $this->em->getRepository(ImportHeader::class)->find($col_to_remove);
                if ($importHeader) {
                    $this->em->remove($importHeader);
                    $this->em->flush();
                }
            }
            
            return $this->redirectToRoute('view_import', ['groupId' => $import->getGroups()->getId()]);

        }
    }

    #[Route('user/import/detail/{importId}', name: 'detail_import')]
    public function detail($importId)
    {
        $title = "Import Detail";
        $imports = $this->em->getRepository(Import::class)->find($importId);
        $headers = $this->em->getRepository(ImportHeader::class)->findBy(["import" => $importId]);
        $rows = $this->em->getRepository(ImportRow::class)->findBy(["import" => $importId]);
        $data = [];
        foreach ($rows as $item) {
            $data[$item->getId()] = $item->getQrCode();
        }

        $sort_array = [];
        $data_header=[];
        foreach ($headers as $header){
            $data_header[$header->getSort()]['field']=$header->getField();
            $data_header[$header->getSort()]['id']=$header->getId();
            $data_header[$header->getSort()]['sort']=$header->getSort();
            $data_header[$header->getSort()]['import_id']=$header->getImport()->getId();
            $data_header[$header->getSort()]['is_exportable']=$header->isIsExportable();
            $data_header[$header->getSort()]['is_displayable']=$header->isIsDisplayable();
            $data_header[$header->getSort()]['contains_qr']=$header->isContainsQr();

            $sort_array[]=$header->getSort();
        }
        ksort($data_header);
        return $this->render('import/detail.html.twig', array('title' => $title, 'importId' => $importId, 'records' => $imports, 'headers' => $data_header, 'rows' => $rows, 'qr' => $data,'sort_array'=>$sort_array));
    }

    #[Route('user/import/add/{groupId}', name: 'add_import')]
    public function add($groupId)
    {
        $title = 'Add Import';
        $edit = false;
        return $this->render('import/add.html.twig', ['title' => $title, 'groupId' => $groupId, 'edit' => $edit]);
    }

    #[Route('user/import/edit/{importId}', name: 'edit_import')]
    public function edit($importId)
    {
        $title = 'Edit Import';
        $edit = true;
        $import = $this->em->getRepository(Import::class)->find($importId);
        $groupId = $import->getGroups()->getId();
        return $this->render('import/add.html.twig', ['title' => $title, 'groupId' => $groupId, 'import' => $import, 'edit' => $edit]);
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
                    foreach ($data['header'] as $key=>$head_data) {
                        $header = new ImportHeader();
                        $header->setField($head_data);
                        $header->setImport($import);
                        $header->setIsExportable(1);
                        $header->setIsDisplayable(1);
                        $header->setSort(++$key);
                        $header->setContainsQr($request->request->has('contains_qr'));
                        $this->em->persist($header);
                    }
                    foreach ($data['rows'] as $item) {
                        $row = new ImportRow();
                        $row->setData(serialize($item));
                        $row->setImport($import);
                        $row->setHeader($header);
                        if($request->request->has('contains_qr')){
                            $row->setQrCode($item[0]);
                        }else{
                            $row->setQrCode(hash('md5',implode(", ", $item)));
                        }
                        $row->setContainQr($request->request->has('contains_qr') ? 1 : 0);
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
            return $this->redirectToRoute('confirm_import_view', ['importId' => $import->getId()]);

            // return $this->redirectToRoute('view_import', ['groupId' => $request->get('groupId')]);
        }

        $this->addFlash(
            'error',
            'Token Mismatch!'
        );
        return $this->redirectToRoute('view_import', ['groupId' => $request->get('groupId')]);
    }

    #[Route('user/import/update', name: 'update_import', methods: ['POST'])]
    public function update(Request $request)
    {
        $submittedToken = $request->get('token');
        if ($this->isCsrfTokenValid('update_import', $submittedToken)) {

            $import = $this->em->getRepository(Import::class)->find($request->get('importId'));
            $import->setName($request->get('name'));
            $this->em->persist($import);
            $this->em->flush();
            $this->addFlash(
                'success',
                'Updated Successfully!'
            );
            return $this->redirectToRoute('view_import', ['groupId' => $request->get('groupId')]);
        }

        $this->addFlash(
            'error',
            'Token Mismatch!'
        );
        return $this->redirectToRoute('view_import', ['groupId' => $request->get('groupId')]);
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
            $rows = $this-> em->getRepository(ImportRow::class)->findBy(['import'=>$import]);
            foreach($rows as $row){
                $this->em->remove($row);
            }
            
            $headers = $this->em->getRepository(ImportHeader::class)->findBy(['import'=>$import]);
            foreach($headers as $header){
                $this->em->remove($header);
            }
            $import->getGroups()->removeImport($import);
            // $this->em->remove($import);
            $this->em->flush();
        }
        $this->addFlash(
            'success',
            'Import Deleted Successfully!'
        );
        return $this->redirectToRoute('view_import', ['groupId' => $groupId]);
    }

    #[Route('user/import/single/add/{importId}', name: 'single_add_import')]
    public function single_add($importId)
    {
        $title = "New Add";
        $headers = $this->em->getRepository(ImportHeader::class)->findBy(['import' => $importId]);
        $fields = [];
        foreach($headers as $header){
            $fields[$header->getSort()] = $header->getField();
        }
        $edit = false;
        ksort($fields);
        return $this->render('import/single_add.html.twig', array('title' => $title, 'header' => $header, 'fields' => $fields, 'edit' => $edit));
    }

    #[Route('user/import/single/edit/{rowId}', name: 'single_edit_import')]
    public function single_edit($rowId)
    {
        $title = "Edit";
        $row = $this->em->getRepository(ImportRow::class)->find($rowId);
        $headers = $this->em->getRepository(ImportHeader::class)->findBy(['import' => $row->getImport()]);
        $fields = [];
        foreach($headers as $header){
            $fields[$header->getSort()] = $header->getField();
        }
        $records = unserialize($row->getData());
        $edit = true;
        ksort($fields);
        
        return $this->render('import/single_add.html.twig', array('title' => $title, 'header' => $header, 'row' => $row, 'edit' => $edit, 'fields' => $fields, 'records' => $records));
    }

    #[Route('user/import/single/create', name: 'single_create_import')]
    public function single_create(Request $request)
    {
        $submittedToken = $request->get('token');
        if ($this->isCsrfTokenValid('single_create_import', $submittedToken)) {
            $import = $this->em->getRepository(Import::class)->find($request->get('importId'));
            $header = $this->em->getRepository(ImportHeader::class)->find($request->get('headerId'));
            $data = $request->get('data');
            $row = new ImportRow();
            $row->setData(serialize($data));
            $row->setImport($import);
            $row->setHeader($header);
            if(array_key_exists("qr",$data)){
                $row->setContainQr(1);
                $row->setQrCode($data['qr']);
            }else{
                $row->setContainQr(0);
                $row->setQrCode(hash('md5',implode(", ", $data)));
            }
            $this->em->persist($row);
            $this->em->flush();
            $this->addFlash(
                'success',
                'Created Successfully!'
            );
        }
        return $this->redirectToRoute('detail_import', ['importId' => $request->get('importId')]);
    }

    #[Route('user/import/single/update', name: 'single_update_import')]
    public function single_update(Request $request)
    {
        $submittedToken = $request->get('token');
        if ($this->isCsrfTokenValid('single_update_import', $submittedToken)) {
            $import = $this->em->getRepository(Import::class)->find($request->get('importId'));
            $header = $this->em->getRepository(ImportHeader::class)->find($request->get('headerId'));
            $data = $request->get('data');
            $row = $this->em->getRepository(ImportRow::class)->find($request->get('rowId'));
            $row->setData(serialize($data));
            $row->setImport($import);
            $row->setHeader($header);
            $this->em->persist($row);

            $this->em->flush();
            $this->addFlash(
                'success',
                'Updated Successfully!'
            );
        }
        return $this->redirectToRoute('detail_import', ['importId' => $request->get('importId')]);
    }

    #[Route('user/import/single/delete/{rowId}', name: 'single_delete_import')]
    public function single_delete($rowId)
    {
        $importRow = $this->em->getRepository(ImportRow::class)->find($rowId);
        if ($importRow) {
            $importId = $importRow->getImport()->getId();
            $this->em->remove($importRow);
            $this->em->flush();
        }
        $this->addFlash(
            'success',
            'Deleted Successfully!'
        );
        return $this->redirectToRoute('detail_import', ['importId' => $importId]);
    }

    #[Route('user/update/column', name: 'update_columns')]
    public function update_columns(Request $request)
    {
        $submittedToken = $request->get('token');
        if ($this->isCsrfTokenValid('update_columns', $submittedToken)) {
            $field = $request->get('field');
            $sort = $request->get('sort');
            $import_id = $request->get('import_id');
            $is_exportable = $request->get('is_exportable')!=null ? 1 : 0;
            $is_displayable = $request->get('is_displayable')!=null ? 1 : 0;
            
            $old_sort_header = $this->em->getRepository(ImportHeader::class)->findOneBy(['sort'=>$request->get('sort'),'import'=>$import_id]);
            
            $header = $this->em->getRepository(ImportHeader::class)->find($request->get('column_id'));

            $current_sort = $header->getSort();

            $old_sort_header->setSort($current_sort);
            $this->em->persist($old_sort_header);

            $header->setField($field);
            $header->setSort($sort);
            $header->setIsExportable($is_exportable);
            $header->setIsDisplayable($is_displayable);
            $this->em->persist($header);

            $this->em->flush();
            $this->addFlash(
                'success',
                'Updated Successfully!'
            );
        }
        return $this->redirectToRoute('detail_import', ['importId' => $import_id]);
    }
}
