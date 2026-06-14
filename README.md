# 🧪 vuln-code-lab

A curated collection of intentionally vulnerable code snippets and mini web apps for demonstrating common web application security vulnerabilities. Each example includes a vulnerable version and, where applicable, a secure variant for comparison.

> ⚠️ **Warning:** This code is intentionally vulnerable. Run only in isolated environments for educational or testing purposes.


## 🔍 Included Vulnerabilities

| Vulnerability | Folder | Description |
|------------------------|-----------------------|----------------------------------------------------|
| Clickjacking | `ui-redressing/` | Iframe-based UI redressing without protection |
| Command Injection | `command_injection/` | Shell commands built from user input, with a vulnerable exporter and a complete safe fix |
| XML External Entity | `xxe/` | XML parsers resolving external entities |
| Path Traversal | `path_traversal/` | Files accessed via `../` in user input, including a naive and a complete fix |
| Insecure Deserialization | `insecure_deserialization/` | Python pickle abuse leading to RCE, plus a naive restricted-pickle fix and a JSON-based safe fix |


## 📦 Requirements

- Python 3.7 or higher
  - Flask
  - `lxml` and `defusedxml` only for the XXE-related examples
- PHP 7.0 or higher (for the command injection support bundle export demo)
- Go 1.18 or higher (for path traversal and other backend-related PoCs)


## 🛡️ Disclaimer

This project is for educational purposes only. Do not deploy this code to production systems. All examples are designed to demonstrate how vulnerabilities work so developers and security professionals can better understand and defend against them.

## 📚 License

MIT License – use freely for education, teaching, and awareness.
