from pathlib import Path
from datetime import datetime

try:
    from docx import Document
except ImportError as exc:
    raise SystemExit(
        "python-docx is not installed.\n"
        "Install it with:\n"
        "  pip install python-docx\n"
    ) from exc


BASE_DIR = Path(__file__).resolve().parents[1]
INPUT_FILE = BASE_DIR / "docs" / "teacher_assistant_requirements_ar.txt"
OUTPUT_FILE = BASE_DIR / "docs" / "وثيقة-متطلبات-منصة-مساعدات-المعلمين.docx"


IMPLEMENTED_NOW = [
    "تحسينات كبيرة في نظام الامتحانات (توحيد التصحيح، إصلاح أخطاء منطقية، وتحسين النتائج).",
    "إزالة أنواع أسئلة (املأ الفراغ/إجابة قصيرة/مقالي) من مسارات الإدارة والإنشاء.",
    "تحسين مسارات بنوك الأسئلة وإضافة الأسئلة للامتحانات.",
    "تحسينات واجهات متعددة في وضع Dark Mode (لوحات admin وصفحات متنوعة).",
    "إضافة رابط الإحالات في سايدبار الطالب وتحسين صفحة الإحالات.",
    "إصلاح صفحات وتقارير إدارية (attendance/reports) ومعالجة أخطاء علاقات الموديلات.",
    "تحسين جرس الإشعارات في Navbar للطالب/المدرب لعرض بيانات حقيقية من قاعدة البيانات.",
    "تحسين كارد الواجبات في Dashboard الطالب ليعرض الواجبات الفعلية المنشورة.",
    "تحسين صفحة تسجيلات البث المباشر لعرض تسجيلات Cloudflare R2 الحالية.",
]

NOT_IMPLEMENTED_EXACTLY = [
    "تنفيذ Multi-tenant كامل بعزل أكاديميات شامل على مستوى جميع الموديولات (بيانات/صلاحيات/تقارير/شهادات).",
    "تطبيق RBAC + Service Entitlements بشكل مكتمل ومتسق في كل الواجهات والـ API ومنع الوصول المباشر لكل خدمة غير مفعلة.",
    "تدفق تسجيل المعلم كاملًا كما هو موصوف (دعوة + تسجيل ذاتي Pending Approval + إضافة من مشرف أكاديمية ضمن النطاق).",
    "نظام Impersonation للمدير (Access كمعلم) مع سجل تدقيق كامل لبداية/نهاية الجلسة وكل العمليات.",
    "خدمة المناهج التفاعلية بالمستويات والوحدات والمهام مع تقارير تقدم أسبوعية كاملة حسب الأكاديمية.",
    "خدمة كورسات مسجلة مع تتبع مشاهدة تفصيلي + Quiz إلزامي بعد كل فيديو + لوحات متابعة أسبوعية متقدمة.",
    "نظام شهادات Multi-Academy بقوالب منفصلة لكل أكاديمية مع إدارة قوالب متقدمة وسيناريوهات إصدار مرنة.",
    "نظام اجتماعات مباشر متكامل بديل Zoom (Meeting API/SDK + Whiteboard + Attendance report + Transcript/Summary).",
    "دليل معلمين متكامل وفق الوثيقة (فلترة متقدمة جدًا + workflow تواصل/مقابلات + حالات تشغيل كاملة + قيود خصوصية متقدمة).",
    "نظام مدفوعات متكامل وفق الوثيقة لكل سيناريو (Webhooks كاملة، Refund flows، وربط مالي متعدد الأكاديميات على مستوى التقارير).",
    "لوحات تقارير وتحليلات أسبوعية كاملة لكل محور (مناهج/كورسات/جلسات/دليل معلمين) كما هو محدد حرفيًا.",
    "لوحة دعم/تذاكر متكاملة (إن كانت ضمن المرحلة الأولى) حسب المواصفات التفصيلية.",
    "تصدير شامل ومنسق لكل التقارير المطلوبة Excel/PDF لكل الأدوار وبنفس نطاقات الصلاحيات.",
    "تنفيذ خطة Phase 1 و Phase 2 بالكامل وفق نفس بنود الوثيقة دون نقص.",
    "تسليم Wireframes/UI-UX تفصيلية لكل Dashboards والصفحات الأساسية كمخرج تصميم معتمد.",
    "تقديم عرض تقني/مالي كامل (تكلفة + جدول زمني + خيارات مزودين) مدمج داخل المنصة كمخرجات تنفيذ.",
]


UNRELATED_NOTE = (
    "ملاحظة مهمة: أغلب التطويرات المنفذة حاليًا في المنصة هي إصلاحات/تحسينات تشغيلية "
    "وتعديلات واجهات ووظائف قائمة، وليست تنفيذًا مباشرًا لوثيقة منصة "
    "«مساعدات المعلمين الأونلاين» متعددة الأكاديميات المذكورة أدناه."
)


def main() -> None:
    if not INPUT_FILE.exists():
        raise SystemExit(f"Input requirements file not found: {INPUT_FILE}")

    requirements_text = INPUT_FILE.read_text(encoding="utf-8").strip()

    doc = Document()
    doc.add_heading("وثيقة متطلبات منصة مساعدات المعلمين الأونلاين", 0)
    doc.add_paragraph(f"تاريخ الإنشاء: {datetime.now().strftime('%Y-%m-%d %H:%M')}")

    doc.add_heading("القسم 1: نص الوثيقة المرسلة (كما هو)", level=1)
    doc.add_paragraph(requirements_text)

    doc.add_heading("القسم 2: ما تم تنفيذه حاليًا في المنصة", level=1)
    doc.add_paragraph(UNRELATED_NOTE)
    for item in IMPLEMENTED_NOW:
        doc.add_paragraph(item, style="List Bullet")

    doc.add_heading("القسم 3: ما لم يتم تنفيذه بالضبط", level=1)
    doc.add_paragraph(
        "القائمة التالية تعكس البنود غير المنفذة بالكامل وفق نص الوثيقة حرفيًا، "
        "بناءً على حالة التطوير الحالية المذكورة في هذا السياق."
    )
    for item in NOT_IMPLEMENTED_EXACTLY:
        doc.add_paragraph(item, style="List Bullet")

    doc.add_heading("القسم 4: خلاصة", level=1)
    doc.add_paragraph(
        "تم إدراج نص المتطلبات بالكامل + توثيق الوضع الحالي للتنفيذ في المنصة داخل هذا الملف."
    )

    OUTPUT_FILE.parent.mkdir(parents=True, exist_ok=True)
    doc.save(str(OUTPUT_FILE))
    print("Word document created successfully.")


if __name__ == "__main__":
    main()
