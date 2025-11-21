USE `inbolsa_dev`; -- ⬅️ pon aquí el nombre real de tu DB

-- Eventos por QR (para “logs” del panel)
CREATE TABLE IF NOT EXISTS qr_events (
  id INT AUTO_INCREMENT PRIMARY KEY,
  qr_id INT NULL,
  code VARCHAR(64) NOT NULL,
  event ENUM('create','validate','open','revoke') NOT NULL,
  ip VARCHAR(64) NULL,
  ua VARCHAR(255) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX (code),
  INDEX (event),
  INDEX (created_at),
  CONSTRAINT fk_qre_qr FOREIGN KEY (qr_id) REFERENCES qr_codes(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Acelera listados
CREATE INDEX IF NOT EXISTS idx_qr_codes_created_at ON qr_codes (created_at);
