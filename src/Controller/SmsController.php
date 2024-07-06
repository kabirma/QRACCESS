<?php

namespace App\Controller;

use App\Entity\Group;
use App\Entity\ImportRow;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;

class SmsController extends AbstractController
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $entityManagerInterface) {
        $this->em = $entityManagerInterface;
    }
    
    #[Route('send/sms', name: 'send_sms', methods: ['POST'])]
    public function send_sms(Request $request)
    {
        $submittedToken = $request->get('token');
        if ($this->isCsrfTokenValid('send_sms', $submittedToken)) {
            $row_id = $request->get('row_id');
            $code = $request->get('code');
            $phone = $request->get('phone');
            $message = $request->get('message');
            $column = $request->get('column');
            $qr_code = $request->get('qr_code');
            
            $current_row = $this->em->getRepository(ImportRow::class)->find($row_id);
            $import_id = $current_row->getImport()->getId();

            $phone_number = $code . $phone;
            $message .= "\r\n".$qr_code;
            $curl = curl_init();

            curl_setopt_array($curl, array(
              CURLOPT_URL => 'http://www.altiria.net/api/http',
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_ENCODING => '',
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => 0,
              CURLOPT_FOLLOWLOCATION => true,
              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
              CURLOPT_CUSTOMREQUEST => 'POST',
              CURLOPT_POSTFIELDS => 'cmd=sendsms&domainId=abarcando&login=test2%40marketing10.en&passwd=qaz22asdf33&dest='.$phone_number.'&senderId=TESTSENDER&msg='.$message,
              CURLOPT_HTTPHEADER => array(
                'Content-Type: application/x-www-form-urlencoded'
              ),
            ));
            
            $response = curl_exec($curl);
            
            curl_close($curl);

            $this->addFlash(
                'success',
                'SMS Sent Successfully!'
            );
        }
        return $this->redirectToRoute('detail_import', ['importId' => $import_id]);
    }

    #[Route('send/email', name: 'send_email', methods: ['POST'])]
    public function send_email(Request $request, MailerInterface $mailer)
    {
        $submittedToken = $request->get('token');
        if ($this->isCsrfTokenValid('send_email', $submittedToken)) {
            $row_id = $request->get('row_id');
            $code = $request->get('code');
            $email = $request->get('email');
            $message = $request->get('message');
            $column = $request->get('column');
            $qr_code = $request->get('qr_code');
            
            $current_row = $this->em->getRepository(ImportRow::class)->find($row_id);
            $import_id = $current_row->getImport()->getId();

            $message .= "<br><br><img src='".$qr_code."' alt='QR Invitation'>";
            

            $email = (new TemplatedEmail())
                ->from(new Address('info@iamahmer.com', 'QR ACCESS'))
                ->to($email)
                ->subject('QR Code Invitation')
                ->htmlTemplate('sms/email_invitation.html.twig')
                ->context([
                    'message' => $message,
                ]);

            $mailer->send($email);

            $this->addFlash(
                'success',
                'Email Sent Successfully!'
            );
        }
        return $this->redirectToRoute('detail_import', ['importId' => $import_id]);
    }
}
