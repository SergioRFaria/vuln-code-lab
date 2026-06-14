from flask import Flask, request, render_template_string
import pickle

app = Flask(__name__)

PAGE = """
<!doctype html>
<html>
<head>
    <title>NorthBank Vendor Intake</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f8; margin: 0; color: #1f2937; }
        .header { background: #0f3d5e; color: white; padding: 20px 40px; font-size: 24px; font-weight: bold; }
        .container { max-width: 920px; margin: 40px auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 16px rgba(0,0,0,0.08); }
        .summary { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-top: 24px; }
        .summary-card { background: #eef6fb; border-radius: 10px; padding: 18px; }
        .label { color: #6b7280; font-size: 13px; text-transform: uppercase; letter-spacing: 0.04em; margin-bottom: 6px; }
        .value { font-size: 24px; font-weight: bold; }
        .layout { display: grid; grid-template-columns: 1.05fr 0.95fr; gap: 24px; margin-top: 24px; }
        .panel { border: 1px solid #e5e7eb; border-radius: 10px; padding: 22px; }
        .subtle { color: #6b7280; }
        .notice { background: #ecfdf3; border: 1px solid #86efac; color: #166534; padding: 12px; border-radius: 6px; margin-top: 18px; }
        .error { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 12px; border-radius: 6px; margin-top: 18px; }
        form label { display: block; margin-top: 16px; font-weight: bold; }
        input[type=file] { width: 100%; padding: 10px; margin-top: 6px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box; }
        button { margin-top: 22px; background: #0f3d5e; color: white; padding: 12px 18px; border: none; border-radius: 6px; font-size: 15px; cursor: pointer; }
        button:hover { background: #0b2f49; }
        pre { background: #f8fafc; border: 1px solid #e5e7eb; padding: 14px; border-radius: 8px; white-space: pre-wrap; word-break: break-word; }
        @media (max-width: 860px) { .summary, .layout { grid-template-columns: 1fr; } .container { margin: 20px; padding: 20px; } }
    </style>
</head>
<body>
    <div class="header">NorthBank Vendor Intake</div>
    <div class="container">
        <h1>Partner Profile Import</h1>
        <p class="subtle">Operations staff can import serialized partner onboarding records before procurement approval is completed.</p>

        <div class="summary">
            <div class="summary-card"><div class="label">Pending Partners</div><div class="value">9</div></div>
            <div class="summary-card"><div class="label">Imported Today</div><div class="value">27</div></div>
            <div class="summary-card"><div class="label">Review SLA</div><div class="value">1 day</div></div>
        </div>

        <div class="layout">
            <div class="panel">
                <h2>Upload Partner Record</h2>
                <p class="subtle">Accepted format: serialized partner intake package from the legacy broker workflow.</p>
                <form action="/upload" method="post" enctype="multipart/form-data">
                    <label for="picklefile">Serialized record file</label>
                    <input id="picklefile" type="file" name="picklefile">
                    <button type="submit">Import Partner Record</button>
                </form>

                {% if message %}
                    <div class="notice">{{ message }}</div>
                {% endif %}
                {% if error %}
                    <div class="error">{{ error }}</div>
                {% endif %}
            </div>

            <div class="panel">
                <h2>Last Imported Payload</h2>
                {% if parsed %}
                    <pre>{{ parsed }}</pre>
                {% else %}
                    <p class="subtle">No payload has been imported in this session.</p>
                {% endif %}
            </div>
        </div>
    </div>
</body>
</html>
"""


def render_page(parsed=None, message=None, error=None):
    return render_template_string(PAGE, parsed=parsed, message=message, error=error)


@app.route('/')
def index():
    return render_page()

@app.route('/upload', methods=['POST'])
def upload():
    f = request.files.get('picklefile')
    if not f:
        return render_page(error="No serialized record file was provided."), 400

    data = f.read()

    obj = pickle.loads(data)

    return render_page(parsed=repr(obj), message="Partner record imported successfully.")

if __name__ == '__main__':
    app.run(debug=True, port=8090)
