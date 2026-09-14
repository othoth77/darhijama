<?php

namespace Mythos\Core\Analytics;

enum AnalyticsEventType: string
{
    case WhatsappClick = 'whatsapp_click';
    case PublicPageView = 'public_page_view';
    case InvitationView = 'invitation_view';
    case InvitationPublished = 'invitation_published';
    case RsvpSubmitted = 'rsvp_submitted';
    case OrderCreated = 'order_created';
}
