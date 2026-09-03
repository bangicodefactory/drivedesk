<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToTenant;

class Notification extends Model
{
    // Tenant isolation (roadmap Tranche S.1): constrains every query to the
    // caller's tenant, so another tenant's row cannot resolve by id.
    use BelongsToTenant;
    use HasFactory;

    protected $fillable = [
        'module',
        'name',
        'subject',
        'message',
        'short_code',
        'enabled_email',
        'enabled_sms',
        'parent_id',
    ];

    static $modules = [
        'user_create' =>
        [
            'name' => 'New User',
            'short_code' => ['{company_name}', '{company_email}', '{company_phone_number}', '{company_address}', '{company_currency}', '{new_user_name}', '{app_link}', '{username}', '{password}'],
            'subject' => 'Welcome to {company_name}!',
            'templete' => '
                <p><strong>Dear {new_user_name}</strong>,</p><p>&nbsp;</p><blockquote><p>Welcome to {company_name}! We are excited to have you on board and look forward to providing you with an exceptional experience.</p><p>We hope you enjoy your experience with us. If you have any feedback, feel free to share it with us.</p><p>&nbsp;</p><p>Your account details are as follows:</p><p><strong>App Link:</strong> {app_link}</p><p><strong>Username:</strong> {username}</p><p><strong>Password:</strong> {password}</p><p>&nbsp;</p><p>Thank you for choosing {company_name}!</p></blockquote><p>Best regards,</p><p>{company_name}</p><p>{company_email}</p>
            ',
        ],
        'new_driver' => [
            'name' => 'New Driver',
            'short_code' => [
                '{company_name}',
                '{company_email}',
                '{company_phone_number}',
                '{company_address}',
                '{company_currency}',
                '{new_driver_name}'
            ],
            'subject' => 'Welcome to {company_name}!',
            'template' => '
                <p><strong>Dear {new_driver_name},</strong></p><p>&nbsp;</p>
                <blockquote>
                    <p>Welcome to {company_name}! We are excited to have you join our team of professional drivers. Together, we aim to provide exceptional transportation services to our customers while ensuring a rewarding experience for you.</p>
                    <p>Your role is vital to our success, and we are here to support you every step of the way. If you have any questions or need assistance, don\'t hesitate to reach out to us.</p>
                    <p>&nbsp;</p>
                    <p>We look forward to building a successful partnership with you. Thank you for choosing {company_name}!</p>
                </blockquote>
                <p>Best regards,</p>
                <p>The {company_name} Team</p>
                <p>{company_email} | {company_phone_number}</p>
            ',
        ],
        'new_booking' => [
            'name' => 'New Booking',
            'short_code' => [
                '{company_name}',
                '{company_email}',
                '{company_phone_number}',
                '{company_address}',
                '{company_currency}',
                '{driver_name}',
                '{booking_date}',
                '{start_date}',
                '{end_date}',
                '{vehicle_name}',
                '{vehicle_type}',
                '{vehicle_model}',
                '{vehicle_plate_number}',
                '{pickup_address}',
                '{drop_address}',
                '{status}'
            ],
            'subject' => 'Your Booking Confirmation with {company_name}',
            'template' => '
                <p><strong>Dear {driver_name},</strong></p><p>&nbsp;</p>
                <blockquote>
                    <p>We are pleased to inform you about a new booking assigned to you. Below are the booking details:</p>
                    <p><strong>Booking Date:</strong> {booking_date}</p>
                    <p><strong>Pickup Address:</strong> {pickup_address}</p>
                    <p><strong>Drop Address:</strong> {drop_address}</p>
                    <p>&nbsp;</p>
                    <p><strong>Trip Details:</strong></p>
                    <p><strong>Start Date:</strong> {start_date}</p>
                    <p><strong>End Date:</strong> {end_date}</p>
                    <p>&nbsp;</p>
                    <p><strong>Vehicle Information:</strong></p>
                    <p><strong>Vehicle Name:</strong> {vehicle_name}</p>
                    <p><strong>Vehicle Type:</strong> {vehicle_type}</p>
                    <p><strong>Vehicle Model:</strong> {vehicle_model}</p>
                    <p><strong>License Plate:</strong> {vehicle_plate_number}</p>
                    <p>&nbsp;</p>
                    <p><strong>Status:</strong> {status}</p>
                    <p>&nbsp;</p>
                    <p>If you have any questions or need assistance, feel free to contact us at {company_email} or call us at {company_phone_number}.</p>
                    <p>&nbsp;</p>
                    <p>Thank you for being an integral part of {company_name}!</p>
                </blockquote>
                <p>Best regards,</p>
                <p>The {company_name} Team</p>
                <p>{company_email} | {company_phone_number}</p>
            ',
        ],
        'new_agreement' => [
            'name' => 'New Agreement',
            'short_code' => [
                '{company_name}',
                '{company_email}',
                '{company_phone_number}',
                '{company_address}',
                '{company_currency}',
                '{driver_name}',
                '{agreement_start_date}',
                '{agreement_end_date}',
                '{vehicle_name}',
                '{vehicle_type}',
                '{vehicle_model}',
                '{vehicle_plate_number}',
                '{terms_condition}',
                '{status}'
            ],
            'subject' => 'New Agreement with {company_name}',
            'template' => '
                <p><strong>Dear {driver_name},</strong></p><p>&nbsp;</p>
                <blockquote>
                    <p>We are pleased to confirm your new agreement with {company_name}. Below are the details of the agreement:</p>
                    <p><strong>Agreement Period:</strong></p>
                    <p>Start Date: {agreement_start_date}</p>
                    <p>End Date: {agreement_end_date}</p>
                    <p>&nbsp;</p>
                    <p><strong>Vehicle Details:</strong></p>
                    <p>Vehicle Name: {vehicle_name}</p>
                    <p>Vehicle Type: {vehicle_type}</p>
                    <p>Vehicle Model: {vehicle_model}</p>
                    <p>License Plate: {vehicle_plate_number}</p>
                    <p>&nbsp;</p>
                    <p><strong>Status:</strong> {status}</p>
                    <p>&nbsp;</p>
                    <p><strong>Terms and Conditions:</strong></p>
                    <p>{terms_condition}</p>
                    <p>&nbsp;</p>
                    <p>If you have any questions or require clarification regarding the agreement, please feel free to contact us at {company_email} or {company_phone_number}.</p>
                    <p>&nbsp;</p>
                    <p>Thank you for being a part of {company_name}. We look forward to a successful collaboration!</p>
                </blockquote>
                <p>Best regards,</p>
                <p>The {company_name} Team</p>
                <p>{company_email} | {company_phone_number}</p>
            ',
        ],
        'agreement_status' => [
            'name' => 'Agreement Status',
            'short_code' => [
                '{company_name}',
                '{company_email}',
                '{company_phone_number}',
                '{company_address}',
                '{company_currency}',
                '{driver_name}',
                '{agreement_start_date}',
                '{agreement_end_date}',
                '{vehicle_name}',
                '{vehicle_type}',
                '{vehicle_model}',
                '{vehicle_plate_number}',
                '{terms_condition}',
                '{status}'
            ],
            'subject' => 'Update on Your Agreement Status with {company_name}',
            'template' => '
                <p><strong>Dear {driver_name},</strong></p><p>&nbsp;</p>
                <blockquote>
                    <p>We wanted to update you regarding the status of your agreement with {company_name}. Below are the current details:</p>
                    <p>&nbsp;</p>
                    <p><strong>Agreement Details:</strong></p>
                    <p><strong>Start Date:</strong> {agreement_start_date}</p>
                    <p><strong>End Date:</strong> {agreement_end_date}</p>
                    <p><strong>Vehicle Information:</strong></p>
                    <p>Vehicle Name: {vehicle_name}</p>
                    <p>Vehicle Type: {vehicle_type}</p>
                    <p>Vehicle Model: {vehicle_model}</p>
                    <p>License Plate: {vehicle_plate_number}</p>
                    <p>&nbsp;</p>
                    <p><strong>Current Status:</strong> {status}</p>
                    <p>&nbsp;</p>
                    <p><strong>Terms and Conditions:</strong></p>
                    <p>{terms_condition}</p>
                    <p>&nbsp;</p>
                    <p>If you have any questions or require further clarification about this update, feel free to contact us at {company_email} or {company_phone_number}. We are here to assist you.</p>
                    <p>&nbsp;</p>
                    <p>Thank you for being a part of {company_name}!</p>
                </blockquote>
                <p>Best regards,</p>
                <p>The {company_name} Team</p>
                <p>{company_email} | {company_phone_number}</p>
            ',
        ],
        'booking_status' => [
            'name' => 'Booking Status',
            'short_code' => [
                '{company_name}',
                '{company_email}',
                '{company_phone_number}',
                '{company_address}',
                '{company_currency}',
                '{driver_name}',
                '{booking_date}',
                '{start_date}',
                '{end_date}',
                '{vehicle_name}',
                '{vehicle_type}',
                '{vehicle_model}',
                '{vehicle_plate_number}',
                '{pickup_address}',
                '{drop_address}',
                '{status}'
            ],
            'subject' => 'Update on Your Booking Status with {company_name}',
            'template' => '
                <p><strong>Dear {driver_name},</strong></p><p>&nbsp;</p>
                <blockquote>
                    <p>This is to inform you about a change in the status of one of your bookings. Please find the updated details below:</p>
                    <p>&nbsp;</p>
                    <p><strong>Booking Details:</strong></p>
                    <p><strong>Booking Date:</strong> {booking_date}</p>
                    <p><strong>Pickup Address:</strong> {pickup_address}</p>
                    <p><strong>Drop Address:</strong> {drop_address}</p>
                    <p>&nbsp;</p>
                    <p><strong>Trip Information:</strong></p>
                    <p><strong>Start Date:</strong> {start_date}</p>
                    <p><strong>End Date:</strong> {end_date}</p>
                    <p>&nbsp;</p>
                    <p><strong>Vehicle Details:</strong></p>
                    <p><strong>Vehicle Name:</strong> {vehicle_name}</p>
                    <p><strong>Vehicle Type:</strong> {vehicle_type}</p>
                    <p><strong>Vehicle Model:</strong> {vehicle_model}</p>
                    <p><strong>License Plate:</strong> {vehicle_plate_number}</p>
                    <p>&nbsp;</p>
                    <p><strong>Updated Status:</strong> {status}</p>
                    <p>&nbsp;</p>
                    <p>If you have any questions or need further assistance, please don\'t hesitate to contact us at {company_email} or {company_phone_number}.</p>
                    <p>&nbsp;</p>
                    <p>Thank you for your dedication and continued efforts with {company_name}!</p>
                </blockquote>
                <p>Best regards,</p>
                <p>The {company_name} Team</p>
                <p>{company_email} | {company_phone_number}</p>
            ',
        ],





    ];
}
