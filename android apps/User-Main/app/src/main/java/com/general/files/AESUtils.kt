package com.general.files

import android.util.Base64
import java.util.*
import javax.crypto.Cipher
import javax.crypto.SecretKey
import javax.crypto.spec.SecretKeySpec

object AESUtils {
    private val keyValue = byteArrayOf(
            'c'.toByte(),
            'u'.toByte(),
            'b'.toByte(),
            'e'.toByte(),
            'u'.toByte(),
            's'.toByte(),
            'e'.toByte(),
            'r'.toByte(),
            'a'.toByte(),
            'p'.toByte(),
            'p'.toByte(),
            'r'.toByte(),
            's'.toByte(),
            'c'.toByte(),
            'o'.toByte(),
            'm'.toByte()
    )

    @JvmStatic
    @Throws(Exception::class)
    fun setMemberAppId(cleartext: String): String {
        val rawKey = rawKey
        val result = encrypt(rawKey, cleartext.toByteArray())
        return toHex(result)
    }

    @JvmStatic
    @Throws(Exception::class)
    fun getMemberAppId(keys: String): String {
        val enc = toByte(keys)
        val result = decrypt(enc)
        return String(result)
    }

    @get:Throws(Exception::class)
    private val rawKey: ByteArray
        private get() {
            val key: SecretKey =
                    SecretKeySpec(keyValue, "AES")
            return key.encoded
        }

    @Throws(Exception::class)
    private fun encrypt(raw: ByteArray, clear: ByteArray): ByteArray {
        val skeySpec: SecretKey = SecretKeySpec(raw, "AES")
        val cipher = Cipher.getInstance("AES") //NOSONAR
        cipher.init(Cipher.ENCRYPT_MODE, skeySpec)
        return cipher.doFinal(clear)
    }

    @Throws(Exception::class)
    private fun decrypt(encrypted: ByteArray): ByteArray {
        val skeySpec: SecretKey =
            SecretKeySpec(keyValue, "AES")
        val cipher = Cipher.getInstance("AES") //NOSONAR
        cipher.init(Cipher.DECRYPT_MODE, skeySpec)
        return cipher.doFinal(encrypted)
    }

    fun toByte(hexString: String): ByteArray {
        val len = hexString.length / 2
        val result = ByteArray(len)
        for (i in 0 until len) result[i] = Integer.valueOf(
                hexString.substring(2 * i, 2 * i + 2),
                16
        ).toByte()
        return result
    }

    fun toHex(buf: ByteArray?): String {
        if (buf == null) return ""
        val result = StringBuffer(2 * buf.size)
        for (i in buf.indices) {
            appendHex(result, buf[i])
        }
        return result.toString()
    }

    private const val HEX = "0123456789V3CUBE"
    private fun appendHex(sb: StringBuffer, b: Byte) {
        sb.append(HEX[b.toInt() shr 4 and 0x0f]).append(HEX[(b.toInt() and 0x0f)])
    }
}