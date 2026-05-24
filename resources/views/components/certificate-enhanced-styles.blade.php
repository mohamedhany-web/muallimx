<style>
    .mx-cert-enhanced {
        --mx-orange: #E84E0E;
        --mx-orange-light: #FF6B2B;
        --mx-navy: #1B2C6E;
        --mx-navy-light: #2A3F9D;
        --mx-navy-dark: #111E4E;
        --mx-gold: #C9A84C;
        --mx-gold-light: #E8C96A;
        --mx-off-white: #FAFAF8;
        width: 100%;
        max-width: 980px;
        margin: 0 auto;
        font-family: 'Tajawal', 'DejaVu Sans', sans-serif;
        direction: rtl;
    }

    .mx-cert-enhanced .certificate {
        width: 100%;
        background: #fffefb;
        border-radius: 18px;
        position: relative;
        overflow: hidden;
        box-shadow: 0 0 0 1px rgba(201,168,76,0.28), 0 0 0 10px rgba(201,168,76,0.08), 0 24px 60px rgba(0,0,0,0.18);
    }

    .mx-cert-enhanced .certificate::before {
        content: '';
        position: absolute;
        inset: 16px;
        border: 1px solid rgba(201,168,76,0.18);
        border-radius: 12px;
        pointer-events: none;
        z-index: 0;
    }

    .mx-cert-enhanced .top-band {
        background: #1B2C6E;
        padding: 0 40px;
        min-height: 96px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        position: relative;
    }

    .mx-cert-enhanced .top-band-line {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: #C9A84C;
    }

    .mx-cert-enhanced .brand-logo {
        display: flex;
        align-items: center;
        gap: 14px;
        position: relative;
        z-index: 1;
    }

    .mx-cert-enhanced .logo-icon {
        width: 68px;
        height: 68px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 6px;
        border-radius: 18px;
        background: rgba(255,255,255,0.1);
        border: 1px solid rgba(255,255,255,0.15);
    }

    .mx-cert-enhanced .logo-icon img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        border-radius: 12px;
    }

    .mx-cert-enhanced .brand-name {
        font-size: 22px;
        font-weight: 900;
        color: #fff;
        letter-spacing: 2px;
        line-height: 1;
    }

    .mx-cert-enhanced .brand-sub {
        font-size: 10px;
        color: #E8C96A;
        letter-spacing: 2px;
        margin-top: 4px;
    }

    .mx-cert-enhanced .cert-type-label {
        font-size: 9px;
        color: #E8C96A;
        letter-spacing: 2px;
        text-transform: uppercase;
    }

    .mx-cert-enhanced .cert-number {
        font-size: 13px;
        color: rgba(255,255,255,0.75);
        margin-top: 4px;
        letter-spacing: 1px;
    }

    .mx-cert-enhanced .cert-body {
        padding: 44px 56px 36px;
        position: relative;
        background: #FAFAF8;
        z-index: 1;
    }

    .mx-cert-enhanced .cert-body-watermark {
        position: absolute;
        top: 50%;
        left: 50%;
        width: 280px;
        height: 280px;
        transform: translate(-50%, -50%);
        opacity: 0.06;
        pointer-events: none;
        object-fit: contain;
    }

    .mx-cert-enhanced .cert-header {
        text-align: center;
        margin-bottom: 28px;
        position: relative;
        z-index: 2;
    }

    .mx-cert-enhanced .cert-header-eyebrow {
        font-size: 11px;
        letter-spacing: 3px;
        color: #E84E0E;
        font-weight: 700;
        margin-bottom: 10px;
    }

    .mx-cert-enhanced .cert-header-title {
        font-size: 40px;
        font-weight: 900;
        color: #111E4E;
        line-height: 1.15;
        margin-bottom: 8px;
    }

    .mx-cert-enhanced .cert-header-sub {
        font-size: 12px;
        color: #666;
    }

    .mx-cert-enhanced .header-badge {
        display: inline-block;
        margin-top: 12px;
        padding: 8px 16px;
        border-radius: 999px;
        background: rgba(232,78,14,0.10);
        border: 1px solid rgba(232,78,14,0.16);
        color: #111E4E;
        font-size: 11px;
        font-weight: 700;
    }

    .mx-cert-enhanced .divider {
        display: flex;
        align-items: center;
        gap: 12px;
        margin: 18px 0 24px;
    }

    .mx-cert-enhanced .divider-line {
        flex: 1;
        height: 1px;
        background: #C9A84C;
        opacity: 0.7;
    }

    .mx-cert-enhanced .divider-diamond {
        width: 8px;
        height: 8px;
        background: #C9A84C;
        transform: rotate(45deg);
    }

    .mx-cert-enhanced .recipient-section {
        text-align: center;
        margin: 8px 0 24px;
    }

    .mx-cert-enhanced .recipient-label {
        font-size: 12px;
        color: #888;
        letter-spacing: 2px;
        margin-bottom: 8px;
    }

    .mx-cert-enhanced .recipient-name {
        font-size: 36px;
        font-weight: 700;
        color: #1B2C6E;
        line-height: 1.2;
        margin-bottom: 6px;
    }

    .mx-cert-enhanced .recipient-underline {
        width: 180px;
        height: 2px;
        background: #E84E0E;
        margin: 0 auto;
    }

    .mx-cert-enhanced .info-section {
        text-align: center;
        margin-bottom: 22px;
    }

    .mx-cert-enhanced .course-title {
        font-size: 18px;
        font-weight: 700;
        color: #1B2C6E;
        margin-bottom: 6px;
    }

    .mx-cert-enhanced .completion-text {
        font-size: 14px;
        color: #555;
        line-height: 1.7;
        max-width: 620px;
        margin: 0 auto 12px;
    }

    .mx-cert-enhanced .completion-text span {
        color: #E84E0E;
        font-weight: 700;
    }

    .mx-cert-enhanced .details-row {
        display: table;
        width: 100%;
        border: 1px solid rgba(201,168,76,0.28);
        border-radius: 12px;
        overflow: hidden;
        background: #fff;
        margin: 8px 0 28px;
    }

    .mx-cert-enhanced .details-row-inner {
        display: table-row;
    }

    .mx-cert-enhanced .detail-item {
        display: table-cell;
        width: 25%;
        padding: 16px 12px;
        text-align: center;
        vertical-align: middle;
        border-left: 1px solid rgba(201,168,76,0.2);
    }

    .mx-cert-enhanced .detail-item:first-child {
        border-left: none;
    }

    .mx-cert-enhanced .detail-label {
        font-size: 9px;
        color: #999;
        letter-spacing: 1.5px;
        display: block;
        margin-bottom: 4px;
    }

    .mx-cert-enhanced .detail-value {
        font-size: 13px;
        font-weight: 700;
        color: #111E4E;
        display: block;
    }

    .mx-cert-enhanced .cert-footer {
        display: table;
        width: 100%;
        border-top: 1px solid rgba(201,168,76,0.2);
        padding-top: 18px;
    }

    .mx-cert-enhanced .cert-footer-row {
        display: table-row;
    }

    .mx-cert-enhanced .signature-block,
    .mx-cert-enhanced .seal,
    .mx-cert-enhanced .qr-block {
        display: table-cell;
        vertical-align: bottom;
        text-align: center;
        width: 25%;
        padding: 0 8px;
    }

    .mx-cert-enhanced .signature-name {
        font-size: 17px;
        font-style: italic;
        color: #111E4E;
        border-bottom: 1px solid #111E4E;
        padding-bottom: 6px;
        margin-bottom: 6px;
        display: inline-block;
        min-width: 140px;
    }

    .mx-cert-enhanced .signature-label {
        font-size: 10px;
        color: #888;
        letter-spacing: 1.5px;
    }

    .mx-cert-enhanced .seal-circle {
        width: 86px;
        height: 86px;
        border-radius: 50%;
        border: 3px solid #C9A84C;
        background: #1B2C6E;
        display: inline-flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        margin: 0 auto;
    }

    .mx-cert-enhanced .seal-text-main {
        font-size: 8px;
        color: #E8C96A;
        letter-spacing: 1px;
        text-align: center;
        line-height: 1.3;
    }

    .mx-cert-enhanced .seal-star {
        color: #C9A84C;
        font-size: 14px;
        margin: 2px 0;
    }

    .mx-cert-enhanced .seal-year {
        font-size: 11px;
        color: #fff;
        font-weight: 700;
    }

    .mx-cert-enhanced .qr-container {
        width: 86px;
        height: 86px;
        background: #fff;
        padding: 5px;
        border: 1px solid rgba(201,168,76,0.4);
        border-radius: 6px;
        margin: 0 auto;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .mx-cert-enhanced .qr-label {
        font-size: 9px;
        color: #888;
        margin-top: 6px;
        letter-spacing: 1px;
    }

    .mx-cert-enhanced .bottom-strip {
        height: 8px;
        background: #E84E0E;
    }

    .mx-cert-enhanced .preview-watermark {
        position: absolute;
        inset: 0;
        z-index: 50;
        display: flex;
        align-items: center;
        justify-content: center;
        pointer-events: none;
    }

    .mx-cert-enhanced .preview-watermark span {
        font-size: 48pt;
        color: rgba(27,44,110,0.09);
        transform: rotate(-24deg);
        font-weight: bold;
        letter-spacing: 2px;
    }

    @media print {
        .mx-cert-enhanced .certificate {
            box-shadow: none;
        }
    }
</style>
