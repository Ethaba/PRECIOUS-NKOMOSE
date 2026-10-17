
package com.preciousnkomose.restaurant

import android.content.Intent
import android.os.Bundle
import android.widget.Toast
import androidx.activity.enableEdgeToEdge
import androidx.appcompat.app.AppCompatActivity
import androidx.core.view.ViewCompat
import androidx.core.view.WindowInsetsCompat

class home : AppCompatActivity() {

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        enableEdgeToEdge()
        setContentView(R.layout.activity_home)

        val mainView = findViewById<android.view.View>(R.id.main)

        ViewCompat.setOnApplyWindowInsetsListener(mainView) { view, insets ->
            val systemBars = insets.getInsets(
                WindowInsetsCompat.Type.systemBars()
            )

            view.setPadding(
                systemBars.left,
                systemBars.top,
                systemBars.right,
                systemBars.bottom
            )

            insets
        }

        setupNavigation()
    }

    private fun setupNavigation() {
        findViewById<android.view.View?>(R.id.nav_home)?.setOnClickListener {
            // Already on the Home screen.
        }

        findViewById<android.view.View?>(R.id.nav_menu)?.setOnClickListener {
            startActivity(Intent(this, menu::class.java))
        }

        findViewById<android.view.View?>(R.id.nav_reserve)?.setOnClickListener {
            startActivity(Intent(this, PickTableActivity::class.java))
        }

        findViewById<android.view.View?>(R.id.nav_catering)?.setOnClickListener {
            Toast.makeText(
                this,
                "Catering will be available in a future update.",
                Toast.LENGTH_SHORT
            ).show()
        }

        findViewById<android.view.View?>(R.id.nav_profile)?.setOnClickListener {
            Toast.makeText(
                this,
                "Profile functionality will be added later.",
                Toast.LENGTH_SHORT
            ).show()
        }

        findViewById<android.view.View?>(R.id.btn_reserve_now)?.setOnClickListener {
            startActivity(Intent(this, PickTableActivity::class.java))
        }

        findViewById<android.view.View?>(R.id.btn_explore_menu)?.setOnClickListener {
            startActivity(Intent(this, menu::class.java))
        }
    }

    fun openMenuFromPreview(view: android.view.View) {
        startActivity(Intent(this, menu::class.java))
    }

}
