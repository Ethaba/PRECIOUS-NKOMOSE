package com.preciousnkomose.restaurant

import android.os.Bundle
import android.util.Patterns
import android.widget.Button
import android.widget.EditText
import android.widget.TextView
import android.widget.Toast
import androidx.appcompat.app.AppCompatActivity
import android.content.Intent

class MainActivity : AppCompatActivity() {

    private lateinit var emailInput: EditText
    private lateinit var passwordInput: EditText
    private lateinit var loginButton: Button
    private lateinit var registerText: TextView
    private lateinit var forgotPasswordText: TextView

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_main)

        emailInput = findViewById(R.id.emailInput)
        passwordInput = findViewById(R.id.passwordInput)
        loginButton = findViewById(R.id.loginButton)
        registerText = findViewById(R.id.registerText)
        forgotPasswordText = findViewById(R.id.forgotPasswordText)

        loginButton.setOnClickListener {
            validateLoginForm()
        }

        registerText.setOnClickListener {
            startActivity(Intent(this, RegisterActivity::class.java))
        }

        forgotPasswordText.setOnClickListener {
            Toast.makeText(
                this,
                "Password recovery will be implemented with authentication.",
                Toast.LENGTH_SHORT
            ).show()
        }
    }

    private fun validateLoginForm() {
        val email = emailInput.text.toString().trim()
        val password = passwordInput.text.toString()

        if (email.isEmpty()) {
            emailInput.error = "Please enter your email address"
            emailInput.requestFocus()
            return
        }

        if (!Patterns.EMAIL_ADDRESS.matcher(email).matches()) {
            emailInput.error = "Please enter a valid email address"
            emailInput.requestFocus()
            return
        }

        if (password.isEmpty()) {
            passwordInput.error = "Please enter your password"
            passwordInput.requestFocus()
            return
        }

        Toast.makeText(
            this,
            "Form validated. Authentication is not connected yet.",
            Toast.LENGTH_LONG
        ).show()
    }
}