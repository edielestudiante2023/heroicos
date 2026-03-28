<?php

namespace App\Libraries;

use App\Models\EmailEnviadoModel;
use App\Models\PlantillaEmailModel;
use SendGrid;
use SendGrid\Mail\Mail;
use SendGrid\Mail\TrackingSettings;
use SendGrid\Mail\ClickTracking;

class SendGridService
{
    protected string $apiKey;
    protected string $fromEmail;
    protected string $fromName;
    protected EmailEnviadoModel $emailModel;
    protected PlantillaEmailModel $plantillaModel;

    public function __construct()
    {
        $this->apiKey    = env('sendgrid.apiKey', '');
        $this->fromEmail = env('sendgrid.fromEmail', '');
        $this->fromName  = env('sendgrid.fromName', 'Academia Heroicos');
        $this->emailModel    = new EmailEnviadoModel();
        $this->plantillaModel = new PlantillaEmailModel();
    }

    /**
     * Send email using a database template
     *
     * @param string|array $destinatario Email string or ['email' => '', 'nombre' => '']
     * @param string $codigoPlantilla Template code from plantillas_email table
     * @param array $variables Key-value pairs to replace {{variable}} in template
     * @return bool
     */
    public function enviar($destinatario, string $codigoPlantilla, array $variables = []): bool
    {
        $plantilla = $this->plantillaModel->getByCode($codigoPlantilla);

        if (!$plantilla) {
            log_message('error', "SendGrid: Plantilla '{$codigoPlantilla}' no encontrada");
            return false;
        }

        $asunto = $this->reemplazarVariables($plantilla['asunto'], $variables);
        $cuerpoPlantilla = $this->reemplazarVariables($plantilla['cuerpo_html'], $variables);
        $cuerpoHtml = $this->wrapInBaseTemplate($cuerpoPlantilla);

        return $this->enviarEmail($destinatario, $asunto, $cuerpoHtml, $codigoPlantilla);
    }

    /**
     * Send email with attachment using a database template
     */
    public function enviarConAdjunto($destinatario, string $codigoPlantilla, array $variables, string $archivoPath, string $archivoNombre = ''): bool
    {
        $plantilla = $this->plantillaModel->getByCode($codigoPlantilla);

        if (!$plantilla) {
            log_message('error', "SendGrid: Plantilla '{$codigoPlantilla}' no encontrada");
            return false;
        }

        $asunto = $this->reemplazarVariables($plantilla['asunto'], $variables);
        $cuerpoPlantilla = $this->reemplazarVariables($plantilla['cuerpo_html'], $variables);
        $cuerpoHtml = $this->wrapInBaseTemplate($cuerpoPlantilla);

        return $this->enviarEmail($destinatario, $asunto, $cuerpoHtml, $codigoPlantilla, $archivoPath, $archivoNombre);
    }

    /**
     * Send a direct email without using a template
     */
    public function enviarDirecto($destinatario, string $asunto, string $contenidoHtml): bool
    {
        $cuerpoHtml = $this->wrapInBaseTemplate($contenidoHtml);
        return $this->enviarEmail($destinatario, $asunto, $cuerpoHtml, null);
    }

    /**
     * Core email sending method
     */
    protected function enviarEmail($destinatario, string $asunto, string $cuerpoHtml, ?string $plantillaCodigo, ?string $archivoPath = null, string $archivoNombre = ''): bool
    {
        $destEmail  = is_array($destinatario) ? $destinatario['email'] : $destinatario;
        $destNombre = is_array($destinatario) ? ($destinatario['nombre'] ?? '') : '';

        // Log the email attempt
        $emailId = $this->emailModel->insert([
            'destinatario_email'  => $destEmail,
            'destinatario_nombre' => $destNombre,
            'asunto'              => $asunto,
            'cuerpo'              => $cuerpoHtml,
            'plantilla'           => $plantillaCodigo,
            'estado'              => 'pendiente',
            'intentos'            => 1,
        ]);

        if (!$this->apiKey) {
            log_message('error', 'SendGrid: API key not configured');
            $this->emailModel->update($emailId, [
                'estado' => 'fallido',
                'error'  => 'API key not configured',
            ]);
            return false;
        }

        try {
            $email = new Mail();
            $email->setFrom($this->fromEmail, $this->fromName);
            $email->setSubject($asunto);
            $email->addTo($destEmail, $destNombre);
            $email->addContent('text/html', $cuerpoHtml);

            // Disable click tracking to prevent URL rewriting
            $trackingSettings = new TrackingSettings();
            $clickTracking = new ClickTracking();
            $clickTracking->setEnable(false);
            $clickTracking->setEnableText(false);
            $trackingSettings->setClickTracking($clickTracking);
            $email->setTrackingSettings($trackingSettings);

            // Add attachment if provided
            if ($archivoPath && file_exists($archivoPath)) {
                $fileContent = base64_encode(file_get_contents($archivoPath));
                $mimeType = mime_content_type($archivoPath) ?: 'application/octet-stream';
                $fileName = $archivoNombre ?: basename($archivoPath);
                $email->addAttachment($fileContent, $mimeType, $fileName);
            }

            $sg = new SendGrid($this->apiKey);
            $response = $sg->send($email);
            $statusCode = $response->statusCode();

            if ($statusCode >= 200 && $statusCode < 300) {
                // Get SendGrid message ID from headers
                $headers = $response->headers();
                $sgMessageId = $headers['x-message-id'] ?? null;

                $this->emailModel->update($emailId, [
                    'estado'      => 'enviado',
                    'sendgrid_id' => $sgMessageId,
                ]);

                log_message('info', "SendGrid: Email enviado a {$destEmail} (plantilla: {$plantillaCodigo})");
                return true;
            }

            // Failed
            $errorBody = $response->body();
            $this->emailModel->update($emailId, [
                'estado' => 'fallido',
                'error'  => "HTTP {$statusCode}: {$errorBody}",
            ]);

            log_message('error', "SendGrid: Error {$statusCode} enviando a {$destEmail}: {$errorBody}");
            return false;

        } catch (\Exception $e) {
            $this->emailModel->update($emailId, [
                'estado' => 'fallido',
                'error'  => $e->getMessage(),
            ]);

            log_message('error', "SendGrid: Excepción enviando a {$destEmail}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Replace {{variable}} placeholders with actual values
     */
    protected function reemplazarVariables(string $texto, array $variables): string
    {
        foreach ($variables as $key => $value) {
            $texto = str_replace('{{' . $key . '}}', $value, $texto);
        }
        return $texto;
    }

    /**
     * Wrap content in the base HTML email template
     */
    protected function wrapInBaseTemplate(string $contenido): string
    {
        return view('emails/base', ['contenido' => $contenido]);
    }
}
